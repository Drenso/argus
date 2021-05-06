<template>
  <b-card header-bg-variant="primary" header-text-variant="white">
    <template #header>
      <div class="d-flex">
        <div class="flex-fill">
          {{ 'project.title.list'|trans }}
        </div>
        <div class="ml-2">
          <a class="pointer text-white" v-if="projects !== null"
             v-b-tooltip.hover.left
             :title="'general.refresh'|trans"
             @click="refresh">
            <font-awesome-icon icon="sync-alt" fixed-width :spin="refreshing"/>
          </a>
        </div>
      </div>
    </template>

    <div class="text-center" v-if="projects === null">
      <LoadingOverlayIcon/>
    </div>
    <div v-else>
      <LoadingOverlay :show="isBusy">
        <div class="d-flex flex-wrap m-n1">
          <div class="flex-fill m-1 filter">
            <b-input autofocus v-model="filter" :placeholder="'general.filter'|trans" @keydown.esc="filter = ''"/>
          </div>
          <div class="flex-shrink-0 flex-grow-1 m-1" v-show="rows > perPage">
            <b-pagination
                class="mb-0"
                v-model="currentPage"
                align="fill"
                :total-rows="rows" :per-page="perPage"/>
          </div>
        </div>

        <b-table
            small class="mt-2 mb-0" show-empty
            sort-by="last_event" sort-desc
            stacked="md"
            :filter="filter"
            :fields="fields" :items="projects" :per-page="perPage" :current-page="currentPage"
            @filtered="onFiltered">
          <template #cell(current_state)="row">
            <a href="#" v-b-tooltip.hover.topright
               :title="`project-environment.state.${row.value}`|trans"
               :class="stateColor(row.value)"
               @click="row.toggleDetails">
              <font-awesome-icon icon="circle" fixed-width/>
            </a>
            <a class="pointer ml" href="#" v-b-tooltip.hover.topright
               :title="'project.button.environments'|trans"
               :class="stateColor(row.value)"
               @click="row.toggleDetails">
              <font-awesome-icon icon="caret-down" fixed-width :rotation="row.item._showDetails ? null : 90"/>
            </a>
          </template>

          <template #cell(_actions)="row">
            <a class="pointer text-primary" target="_blank"
               v-b-tooltip.hover.topleft
               :title="'project.button.gitlab'|trans"
               :href="row.item._gitlab_url">
              <font-awesome-icon :icon="['fab', 'gitlab']" fixed-width/>
            </a>

            <a class="pointer text-secondary"
               v-b-tooltip.hover.topleft
               :title="'project.button.resync'|trans"
               @click="resyncProject(row.item)">
              <font-awesome-icon :icon="resyncing[row.item.id] ? 'circle-notch' : 'sync'"
                                 fixed-width :spin="resyncing[row.item.id]"/>
            </a>

            <a class="pointer text-danger"
               v-b-tooltip.hover.topleft
               :title="'project.button.delete'|trans"
               @click="deleteProject(row.item)">
              <font-awesome-icon :icon="deleting[row.item.id] ? 'circle-notch' : 'trash'"
                                 fixed-width :spin="deleting[row.item.id]"/>
            </a>
          </template>

          <template #row-details="row">
            <div class="px-3">
              <h6>
                {{ 'project-environment.title._multiple'|trans }}

                <a class="pointer text-secondary"
                   v-b-tooltip.hover.right
                   :title="'project-environment.button.refresh'|trans"
                   @click="refreshProjectEnvironments(row.item)">
                  <font-awesome-icon :icon="refreshEnvironments[row.item.id] ? 'circle-notch' : 'redo'"
                                     fixed-width :spin="refreshEnvironments[row.item.id]"/>
                </a>
              </h6>

              <span v-if="row.item.environments.length === 0" class="font-italic">
                {{ 'project-environment.text.none'|trans }}
              </span>
              <div v-else>
                <b-table
                    small class="mt-2 mb-0"
                    sort-by="name" sort-asc
                    stacked="md"
                    :fields="environmentFields" :items="row.item.environments">
                  <template #cell(current_state)="row">
                    <a href="#" v-b-tooltip.hover.topright
                       :title="`project-environment.state.${row.value}`|trans"
                       :class="stateColor(row.value)">
                      <font-awesome-icon icon="circle" fixed-width/>
                    </a>
                  </template>
                </b-table>
              </div>
            </div>
          </template>
        </b-table>

        <div class="text-right">
          <b-button variant="success" @click="addProject">
            <font-awesome-icon icon="plus" fixed-width/>
            {{ 'general.add'|trans }}
          </b-button>
        </div>
      </LoadingOverlay>
    </div>

    <ValidationObserver slim ref="addObserver">
      <b-modal
          ref="addProjectModal" body-class="p-0"
          static hide-footer
          :hide-header-close="adding"
          :no-close-on-esc="adding"
          :no-close-on-backdrop="adding"
          :title="'project.title.add'|trans"
          @shown="projectField.focus()">
        <LoadingOverlay :show="adding">
          <form class="p-3" @submit.prevent="doAddProject">
            <ErrorAlert :show="errorMessage !== null" :text="errorMessage"/>

            <ValidatedField
                rules="required"
                :label="'project.field.name'|trans" :help="'project.help.name'|trans">
              <b-input ref="projectField" v-model="projectName" autofocus trim/>
            </ValidatedField>

            <div class="d-flex flex-wrap justify-content-end m-n1">
              <div class="flex-shrink-0 m-1">
                <b-button variant="secondary" @click="addProjectModal.hide()">
                  <font-awesome-icon icon="times" fixed-width/>
                  {{ 'general.cancel'|trans }}
                </b-button>
              </div>
              <div class="flex-shrink-0 m-1">
                <b-button variant="success" @click="doAddProject">
                  <font-awesome-icon icon="plus" fixed-width/>
                  {{ 'general.add'|trans }}
                </b-button>
              </div>
            </div>
          </form>
        </LoadingOverlay>
      </b-modal>
    </ValidationObserver>
  </b-card>
</template>

<script lang="ts">
  import {BFormInput, BModal, BvTableFieldArray} from 'bootstrap-vue';
  import {ValidationObserver} from 'vee-validate';
  import {Component, Ref, Vue} from 'vue-property-decorator';
  import {Project, ProjectEnvironmentState} from '../../../api/ProjectTypes';
  import ErrorAlert from '../../../components/alerts/ErrorAlert.vue';
  import ValidatedField from '../../../components/form/ValidatedField.vue';
  import LoadingOverlay from '../../../components/layout/LoadingOverlay.vue';
  import LoadingOverlayIcon from '../../../components/layout/LoadingOverlayIcon.vue';

  @Component({
    components: {ErrorAlert, ValidatedField, LoadingOverlayIcon, LoadingOverlay},
  })
  export default class ProjectCard extends Vue {
    public adding: boolean = false;
    public refreshing: boolean = false;
    public refreshEnvironments: { [projectId: number]: boolean } = {};
    public resyncing: { [projectId: number]: boolean } = {};
    public deleting: { [projectId: number]: boolean } = {};
    public projects: Project[] | null = null;

    public filter: string = '';
    public rows: number = 0;
    public perPage: number = 5;
    public currentPage: number = 1;

    public projectName: string = '';

    public errorMessage: string | null = null;

    @Ref()
    private readonly addObserver!: InstanceType<typeof ValidationObserver>;

    @Ref()
    private readonly addProjectModal!: BModal;

    @Ref()
    private readonly projectField!: BFormInput;

    public mounted() {
      this.loadProjects();
    }

    protected async addProject() {
      this.errorMessage = null;
      this.projectName = '';
      await this.$nextTick();

      this.addObserver.reset();
      this.addProjectModal.show();
    }

    protected async doAddProject() {
      await this.addObserver.handleSubmit(async () => {
        if (this.adding) {
          return;
        }
        this.adding = true;

        try {
          const response = await this.$http.post(this.$sfRouter.generate('app_api_project_add'), {
            name: this.projectName,
          });

          this.projects!.push(response.data);
          this.addProjectModal.hide();
          await this.reloadFilter();
        } catch (e) {
          if (e.response && e.response.status === 400) {
            this.errorMessage = e.response.data.reason;
            return;
          }
          throw e;
        } finally {
          this.adding = false;
        }
      });
    }

    protected onFiltered(filteredItems: Project[]) {
      this.rows = filteredItems.length;
      this.currentPage = 1;
    }

    protected async refresh() {
      if (this.isBusy) {
        return;
      }

      this.refreshing = true;
      try {
        await this.loadProjects();
      } finally {
        this.refreshing = false;
      }
    }

    protected async deleteProject(project: Project) {
      if (this.isBusy) {
        return;
      }

      const ok = await this.$bvModal.msgBoxConfirm(this.$translator.trans('project.text.delete-confirm', {
        project: project.name,
      }));

      if (!ok) {
        return;
      }

      this.deleting[project.id] = true;
      try {
        await this.$http.delete(this.$sfRouter.generate('app_api_project_delete', {project: project.id}));

        const toRemoveIndex = this.projects!.findIndex((p) => p.id === project.id);
        if (toRemoveIndex !== -1) {
          this.projects!.splice(toRemoveIndex, 1);
        }
        await this.reloadFilter();
      } finally {
        this.deleting[project.id] = false;
      }
    }

    protected async refreshProjectEnvironments(project: Project) {
      if (this.isBusy) {
        return;
      }

      const ok = await this.$bvModal.msgBoxConfirm(this.$translator.trans('project-environment.text.refresh-confirm', {
        project: project.name,
      }));

      if (!ok) {
        return;
      }

      this.refreshEnvironments[project.id] = true;
      try {
        const response = await this.$http.post(
            this.$sfRouter.generate('app_api_project_refreshenvironments', {project: project.id}));

        // Update data
        project.current_state = response.data.current_state;
        project.environments = response.data.environments;
      } finally {
        this.refreshEnvironments[project.id] = false;
      }
    }

    protected async resyncProject(project: Project) {
      if (this.isBusy) {
        return;
      }

      const ok = await this.$bvModal.msgBoxConfirm(this.$translator.trans('project.text.resync-confirm', {
        project: project.name,
      }));

      if (!ok) {
        return;
      }

      this.resyncing[project.id] = true;
      try {
        await this.$http.post(this.$sfRouter.generate('app_api_project_sync', {project: project.id}));
      } finally {
        this.resyncing[project.id] = false;
      }
    }

    protected stateColor(state: ProjectEnvironmentState): string {
      switch (state) {
        case 'ok':
          return 'text-success';
        case 'running':
          return 'text-primary';
        case 'failed':
          return 'text-danger';
        default:
          return 'text-secondary';
      }
    }

    private async loadProjects() {
      const response = await this.$http.get(this.$sfRouter.generate('app_api_project_list'));
      this.projects = response.data;
      this.projects!.forEach((p) => {
        Vue.set(this.refreshEnvironments, p.id, false);
        Vue.set(this.resyncing, p.id, false);
        Vue.set(this.deleting, p.id, false);
      });
      this.rows = this.projects!.length;
    }

    private async reloadFilter() {
      const filter = this.filter;
      if (!filter) {
        this.rows = this.projects!.length;
        return;
      }

      this.filter = '';
      await this.$nextTick();
      this.filter = filter;
      await this.$nextTick();
    }

    protected get fields(): BvTableFieldArray {
      return [
        {
          key: 'current_state',
          label: this.$translator.trans('project.field.current-state'),
          sortable: true,
          class: 'project-current-state text-md-center',
        }, {
          key: 'name',
          label: this.$translator.trans('project.field.name'),
          sortable: true,
          class: 'project-name',
        }, {
          key: 'last_event',
          label: this.$translator.trans('project.field.last-event'),
          sortable: true,
          formatter: (value) => {
            return value
                ? this.$moment(value).format('YYYY-MM-DD HH:mm:ss')
                : '-';
          },
          class: 'project-last-event',
        }, {
          key: '_actions',
          label: this.$translator.trans('general.actions'),
          class: 'project-action',
        },
      ];
    }

    protected get environmentFields(): BvTableFieldArray {
      return [
        {
          key: 'current_state',
          label: this.$translator.trans('project.field.current-state'),
          sortable: true,
          class: 'project-current-state text-md-center',
        }, {
          key: 'name',
          label: this.$translator.trans('project-environment.title._'),
          sortable: true,
          class: 'project-name',
        }, {
          key: 'last_event',
          label: this.$translator.trans('project.field.last-event'),
          sortable: true,
          formatter: (value) => {
            return value
                ? this.$moment(value).format('YYYY-MM-DD HH:mm:ss')
                : '-';
          },
          class: 'project-last-event',
        }, {
          key: '_actions',
          label: this.$translator.trans('general.actions'),
          class: 'project-action',
        },
      ];
    }

    protected get isBusy(): boolean {
      return this.refreshing || this.isRefreshingEnvironments || this.isResyncing || this.isDeleting;
    }

    protected get isDeleting(): boolean {
      return Object.values(this.deleting).some((v) => v);
    }

    protected get isRefreshingEnvironments(): boolean {
      return Object.values(this.refreshEnvironments).some((v) => v);
    }

    protected get isResyncing(): boolean {
      return Object.values(this.resyncing).some((v) => v);
    }
  }
</script>

<style lang="scss" scoped>
  @import 'assets/css/variables';

  .filter {
    min-width: calc(100% - #{2 * map_get($spacers, 1)});
    @include media-breakpoint-up(md) {
      min-width: 20rem;
    }
  }

  table {
    /deep/ {

      .project-action {
        margin: -1 * map_get($spacers, 2);

        a {
          margin: map_get($spacers, 1);
        }
      }

      @include media-breakpoint-up(md) {
        .project-last-event {
          white-space: nowrap;
          width: 1px;
          padding-left: map_get($spacers, 3);
          padding-right: map_get($spacers, 5);
        }

        .project-action, .project-current-state {
          white-space: nowrap;
          width: 1px;
        }

        th {
          &.project-action {
            > * {
              display: none;
            }
          }
        }
      }
    }
  }
</style>
