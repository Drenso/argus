<template>
  <b-card header-bg-variant="primary" header-text-variant="white">
    <template #header>
      <div class="d-flex">
        <div class="flex-fill">
          {{ 'project.title.list'|trans }}
        </div>
        <div class="ml-2">
          <a @click="refresh" class="pointer" v-if="projects !== null"
             v-b-tooltip.hover.left :title="'general.refresh'|trans">
            <font-awesome-icon icon="sync-alt" fixed-width :spin="refreshing"/>
          </a>
        </div>
      </div>
    </template>

    <div class="text-center" v-if="projects === null">
      <LoadingOverlayIcon/>
    </div>
    <div v-else>
      <LoadingOverlay :show="refreshing">
        <div class="d-flex flex-wrap m-n1">
          <div class="flex-fill m-1 filter">
            <b-input autofocus v-model="filter" :placeholder="'general.filter'|trans"/>
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
            small class="mt-2 mb-0" fixed show-empty
            sort-by="last_event" sort-desc
            :filter="filter"
            :fields="fields" :items="projects" :per-page="perPage" :current-page="currentPage"
            @filtered="onFiltered"/>

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
  import {Project} from '../../../api/ProjectTypes';
  import ErrorAlert from '../../../components/alerts/ErrorAlert.vue';
  import ValidatedField from '../../../components/form/ValidatedField.vue';
  import LoadingOverlay from '../../../components/layout/LoadingOverlay.vue';
  import LoadingOverlayIcon from '../../../components/layout/LoadingOverlayIcon.vue';

  @Component({
    components: {ErrorAlert, ValidatedField, LoadingOverlayIcon, LoadingOverlay},
  })
  export default class ProjectCard extends Vue {
    public refreshing: boolean = false;
    public adding: boolean = false;
    public projects: Project[] | null = null;

    public filter: string = '';
    public rows: number = 0;
    public perPage: number = 10;
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
      if (this.refreshing) {
        return;
      }

      this.refreshing = true;
      try {
        await this.loadProjects();
      } finally {
        this.refreshing = false;
      }
    }

    private async loadProjects() {
      const response = await this.$http.get(this.$sfRouter.generate('app_api_project_list'));
      this.projects = response.data;
      this.rows = this.projects!.length;
    }

    protected get fields(): BvTableFieldArray {
      return [
        {
          key: 'name',
          label: this.$translator.trans('project.field.name'),
          sortable: true,
        }, {
          key: 'last_event',
          label: this.$translator.trans('project.field.last-event'),
          sortable: true,
          formatter: (value) => {
            return value
                ? this.$moment(value).format('YYYY-MM-DD HH:mm:ss')
                : '-';
          },
        },
      ];
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
</style>
