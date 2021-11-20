<template>
  <b-card
      header-bg-variant="primary"
      header-text-variant="white">
    <template #header>
      <div class="d-flex">
        <div class="flex-fill">
          {{ 'project.title.list'|trans }}
        </div>
        <div class="ml-2">
          <a
              v-if="projects !== null"
              v-b-tooltip.hover.left
              class="pointer text-white"
              :title="'general.refresh'|trans"
              @click="refresh">
            <font-awesome-icon
                fixed-width
                icon="sync-alt"
                :spin="refreshing"/>
          </a>
        </div>
      </div>
    </template>

    <div
        v-if="projects === null"
        class="text-center">
      <LoadingOverlayIcon/>
    </div>
    <div v-else>
      <LoadingOverlay :show="isBusy">
        <div class="d-flex flex-wrap m-n1">
          <div class="flex-fill m-1 filter">
            <b-input
                v-model="filter"
                autofocus
                :placeholder="'general.filter'|trans"
                @keydown.esc="filter = ''"/>
          </div>
          <div
              v-show="rows > perPage"
              class="flex-shrink-0 flex-grow-1 m-1">
            <b-pagination
                v-model="currentPage"
                align="fill"
                class="mb-0"
                :per-page="perPage"
                :total-rows="rows"/>
          </div>
        </div>

        <b-table
            class="mt-2 mb-2"
            :current-page="currentPage"
            :fields="fields"
            :filter="filter"
            :items="projects"
            :per-page="perPage"
            show-empty
            small
            sort-by="last_event"
            sort-desc
            stacked="md"
            @filtered="onFiltered">
          <template #cell(current_state)="row">
            <a
                v-b-tooltip.hover.topright
                :class="stateColor(row.value)"
                href="#"
                :title="`project-environment.state.${row.value}`|trans"
                @click="row.toggleDetails">
              <font-awesome-icon
                  fixed-width
                  icon="circle"/>
            </a>
            <a
                v-b-tooltip.hover.topright
                class="pointer ml"
                :class="stateColor(row.value)"
                href="#"
                :title="'project.button.environments'|trans"
                @click="row.toggleDetails">
              <font-awesome-icon
                  fixed-width
                  icon="caret-down"
                  :rotation="row.item._showDetails ? null : 90"/>
            </a>
          </template>

          <template #cell(_actions)="row">
            <a
                v-b-tooltip.hover.topleft
                class="pointer text-primary"
                :href="row.item._gitlab_url"
                target="_blank"
                :title="'project.button.gitlab'|trans">
              <font-awesome-icon
                  fixed-width
                  :icon="['fab', 'gitlab']"/>
            </a>

            <a
                v-b-tooltip.hover.topleft
                class="pointer text-secondary"
                :title="'project.button.resync'|trans"
                @click="resyncProject(row.item)">
              <font-awesome-icon
                  fixed-width
                  :icon="resyncing[row.item.id] ? 'circle-notch' : 'sync'"
                  :spin="resyncing[row.item.id]"/>
            </a>

            <a
                v-b-tooltip.hover.topleft
                class="pointer text-danger"
                :title="'project.button.delete'|trans"
                @click="deleteProject(row.item)">
              <font-awesome-icon
                  fixed-width
                  :icon="deleting[row.item.id] ? 'circle-notch' : 'trash'"
                  :spin="deleting[row.item.id]"/>
            </a>
          </template>

          <template #row-details="row">
            <div class="px-3">
              <h6>
                {{ 'project-environment.title._multiple'|trans }}

                <a
                    v-b-tooltip.hover.right
                    class="pointer text-secondary"
                    :title="'project-environment.button.refresh'|trans"
                    @click="refreshProjectEnvironments(row.item)">
                  <font-awesome-icon
                      fixed-width
                      :icon="refreshEnvironments[row.item.id] ? 'circle-notch' : 'redo'"
                      :spin="refreshEnvironments[row.item.id]"/>
                </a>
              </h6>

              <span
                  v-if="row.item.environments.length === 0"
                  class="font-italic">
                {{ 'project-environment.text.none'|trans }}
              </span>
              <div v-else>
                <b-table
                    class="mt-2 mb-0"
                    :fields="environmentFields"
                    :items="row.item.environments"
                    small
                    sort-asc
                    sort-by="name"
                    stacked="md">
                  <template #cell(current_state)="item">
                    <a
                        v-b-tooltip.hover.topright
                        :class="stateColor(item.value)"
                        href="#"
                        :title="`project-environment.state.${item.value}`|trans">
                      <font-awesome-icon
                          fixed-width
                          icon="circle"/>
                    </a>
                  </template>
                </b-table>
              </div>
            </div>
          </template>
        </b-table>

        <div class="text-right">
          <b-button
              variant="success"
              @click="addProject">
            <font-awesome-icon
                fixed-width
                icon="plus"/>
            {{ 'general.add'|trans }}
          </b-button>
        </div>
      </LoadingOverlay>
    </div>

    <ValidationObserver
        ref="addObserver"
        slim>
      <b-modal
          ref="addProjectModal"
          body-class="p-0"
          hide-footer
          :hide-header-close="adding"
          :no-close-on-backdrop="adding"
          :no-close-on-esc="adding"
          static
          :title="'project.title.add'|trans"
          @shown="projectField.focus()">
        <LoadingOverlay :show="adding">
          <form
              class="p-3"
              @submit.prevent="doAddProject">
            <ErrorAlert
                :show="errorMessage !== null"
                :text="errorMessage"/>

            <ValidatedField
                :help="'project.help.path'|trans"
                :label="'project.field.path'|trans"
                rules="required">
              <b-input
                  ref="projectField"
                  v-model="projectPath"
                  autofocus
                  trim/>
            </ValidatedField>

            <div class="d-flex flex-wrap justify-content-end m-n1">
              <div class="flex-shrink-0 m-1">
                <b-button
                    variant="secondary"
                    @click="addProjectModal.hide()">
                  <font-awesome-icon
                      fixed-width
                      icon="times"/>
                  {{ 'general.cancel'|trans }}
                </b-button>
              </div>
              <div class="flex-shrink-0 m-1">
                <b-button
                    variant="success"
                    @click="doAddProject">
                  <font-awesome-icon
                      fixed-width
                      icon="plus"/>
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
  import {AxiosError, AxiosResponse} from 'axios';
  import {BFormInput, BModal, BvTableFieldArray} from 'bootstrap-vue';
  import {ValidationObserver} from 'vee-validate';
  import {Component, Ref, Vue} from 'vue-property-decorator';
  import {Project, ProjectEnvironment, ProjectEnvironmentState} from '../../../api/ProjectTypes';
  import ErrorAlert from '../../../components/alerts/ErrorAlert.vue';
  import ValidatedField from '../../../components/form/ValidatedField.vue';
  import LoadingOverlay from '../../../components/layout/LoadingOverlay.vue';
  import LoadingOverlayIcon from '../../../components/layout/LoadingOverlayIcon.vue';

  @Component({
    components: {ErrorAlert, ValidatedField, LoadingOverlayIcon, LoadingOverlay},
  })
  export default class ProjectCard extends Vue {
    protected adding = false;
    protected refreshing = false;
    protected refreshEnvironments: { [projectId: number]: boolean } = {};
    protected resyncing: { [projectId: number]: boolean } = {};
    protected deleting: { [projectId: number]: boolean } = {};
    protected projects: Project[] | null = null;

    protected filter = '';
    protected rows = 0;
    protected perPage = 5;
    protected currentPage = 1;

    protected projectPath = '';

    protected errorMessage: string | null = null;

    @Ref()
    private readonly addObserver!: InstanceType<typeof ValidationObserver>;

    @Ref()
    private readonly addProjectModal!: BModal;

    @Ref()
    private readonly projectField!: BFormInput;

    public mounted(): void {
      this.loadProjects();
    }

    protected async addProject(): Promise<void> {
      this.errorMessage = null;
      this.projectPath = '';
      await this.$nextTick();

      this.addObserver.reset();
      this.addProjectModal.show();
    }

    protected async doAddProject(): Promise<void> {
      await this.addObserver.handleSubmit(async () => {
        if (this.adding) {
          return;
        }
        this.adding = true;

        try {
          const response: AxiosResponse<Project> =
              await this.$http.post(this.$sfRouter.generate('app_api_project_add'), {
                path: this.projectPath,
              });

          this.projects?.push(response.data);
          this.addProjectModal.hide();
          await this.reloadFilter();
        } catch (e) {
          const error = e as AxiosError;
          if (error.response && error.response.status === 400) {
            this.errorMessage = (error.response.data as { reason: string }).reason;
            return;
          }
          throw e;
        } finally {
          this.adding = false;
        }
      });
    }

    protected onFiltered(filteredItems: Project[]): void {
      this.rows = filteredItems.length;
      this.currentPage = 1;
    }

    protected async refresh(): Promise<void> {
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

    protected async deleteProject(project: Project): Promise<void> {
      if (this.isBusy || !this.projects) {
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

        const toRemoveIndex = this.projects.findIndex((p) => p.id === project.id);
        if (toRemoveIndex !== -1) {
          this.projects.splice(toRemoveIndex, 1);
        }
        await this.reloadFilter();
      } finally {
        this.deleting[project.id] = false;
      }
    }

    protected async refreshProjectEnvironments(project: Project): Promise<void> {
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
        const response: AxiosResponse<{ current_state: ProjectEnvironmentState, environments: ProjectEnvironment[] }> =
            await this.$http.post(
                this.$sfRouter.generate('app_api_project_refreshenvironments', {project: project.id}),
            );

        // Update data
        project.current_state = response.data.current_state;
        project.environments = response.data.environments;
      } finally {
        this.refreshEnvironments[project.id] = false;
      }
    }

    protected async resyncProject(project: Project): Promise<void> {
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

    private async loadProjects(): Promise<void> {
      const response: AxiosResponse<Project[]> =
          await this.$http.get(this.$sfRouter.generate('app_api_project_list'));
      this.projects = response.data;
      this.projects?.forEach((p) => {
        Vue.set(this.refreshEnvironments, p.id, false);
        Vue.set(this.resyncing, p.id, false);
        Vue.set(this.deleting, p.id, false);
      });
      this.rows = this.projects?.length ?? 0;
    }

    private async reloadFilter(): Promise<void> {
      const filter = this.filter;
      if (!filter) {
        this.rows = this.projects?.length ?? 0;
        return;
      }

      this.filter = '';
      await this.$nextTick();
      this.filter = filter;
      await this.$nextTick();
    }

    protected get fields(): BvTableFieldArray {
      const fields: BvTableFieldArray = [
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
        },
      ];

      if (window.HAS_MULTIPLE_HOSTS) {
        fields.push({
          key: 'host',
          label: this.$translator.trans('project.field.host'),
          sortable: true,
          class: 'project-host',
          formatter: (value: string) => {
            return value
                ? value
                : this.$translator.trans('general.unknown');
          },
        });
      }

      fields.push(...[{
        key: 'last_event',
        label: this.$translator.trans('project.field.last-event'),
        sortable: true,
        formatter: (value: string) => {
          return value
              ? this.$moment(value).format('YYYY-MM-DD HH:mm:ss')
              : '-';
        },
        class: 'project-last-event',
      }, {
        key: '_actions',
        label: this.$translator.trans('general.actions'),
        class: 'project-action',
      }]);

      return fields;
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
