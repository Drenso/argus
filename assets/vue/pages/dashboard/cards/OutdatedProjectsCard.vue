<template>
  <b-card header-bg-variant="primary" header-text-variant="white">
    <template #header>
      <div class="d-flex">
        <div class="flex-fill">
          {{ 'outdated-project.title._'|trans }}
        </div>
        <div class="ml-2">
          <a @click="refresh" class="pointer text-white" v-if="outdatedProjects !== null"
             v-b-tooltip.hover.left :title="'general.refresh'|trans">
            <font-awesome-icon icon="sync-alt" fixed-width :spin="refreshing"/>
          </a>
        </div>
      </div>
    </template>

    <div class="text-center" v-if="outdatedProjects === null">
      <LoadingOverlayIcon/>
    </div>
    <div v-else>
      <LoadingOverlay :show="refreshing">
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
            small class="mt-2 mb-2" show-empty
            sort-by="name" sort-desc
            stacked="md"
            :filter="filter"
            :fields="fields" :items="outdatedProjects" :per-page="perPage" :current-page="currentPage"
            @filtered="onFiltered">

          <template #cell(_actions)="row">
            <a class="pointer text-secondary"
               v-b-tooltip.hover.topleft
               :title="'outdated-project.button.show-diff'|trans"
               @click="showDiff(row.item)">
              <font-awesome-icon icon="search"/>
            </a>

            <a class="pointer text-primary"
               v-b-tooltip.hover.topleft
               :title="'outdated-project.button.create-mr'|trans"
               @click="createMr(row.item)">
              <font-awesome-icon :icon="creatingMrs[row.item.project.id] ? 'circle-notch' : 'play'"
                                 fixed-width :spin="creatingMrs[row.item.project.id]"/>
            </a>
          </template>
        </b-table>

        <div class="text-right">
          <b-button variant="success" @click="createMrs" :disabled="isBusy">
            <font-awesome-icon :icon="creatingAllMrs ? 'circle-notch' : 'play'" :spin="creatingAllMrs" fixed-width/>
            {{ 'outdated-project.button.create-mrs'|trans }}
          </b-button>
        </div>
      </LoadingOverlay>
    </div>
  </b-card>
</template>

<script lang="ts">
  import {BvTableFieldArray} from 'bootstrap-vue';
  import {Component, Vue} from 'vue-property-decorator';
  import {OutdatedProject} from '../../../api/ProjectTypes';
  import LoadingOverlay from '../../../components/layout/LoadingOverlay.vue';
  import LoadingOverlayIcon from '../../../components/layout/LoadingOverlayIcon.vue';
  import EventStats from '../components/EventStats.vue';

  @Component({
    components: {LoadingOverlay, LoadingOverlayIcon, EventStats},
  })
  export default class OutdatedProjectsCard extends Vue {
    protected refreshing: boolean = false;
    protected outdatedProjects: OutdatedProject[] | null = null;

    protected filter: string = '';
    protected rows: number = 0;
    protected perPage: number = 5;
    protected currentPage: number = 1;

    protected creatingAllMrs: boolean = false;
    protected creatingMrs: { [projectId: number]: boolean } = {};

    public mounted() {
      this.loadOutdatedProjects();
    }

    protected async createMr(item: OutdatedProject) {
      if (this.isBusy) {
        return;
      }

      this.creatingMrs[item.project.id] = true;
      try {
        await this.$http.post(this.$sfRouter.generate('app_api_project_createmr', {project: item.project.id}));
      } finally {
        this.creatingMrs[item.project.id] = false;
      }
    }

    protected async createMrs() {
      if (this.isBusy) {
        return;
      }

      this.creatingAllMrs = true;
      try {
        await this.$http.post(this.$sfRouter.generate('app_api_project_createmrs'));
      } finally {
        this.creatingAllMrs = false;
      }
    }

    protected onFiltered(filteredItems: OutdatedProject[]) {
      this.rows = filteredItems.length;
      this.currentPage = 1;
    }

    protected async refresh() {
      if (this.refreshing) {
        return;
      }

      this.refreshing = true;
      try {
        await this.loadOutdatedProjects();
      } finally {
        this.refreshing = false;
      }
    }

    protected showDiff(project: OutdatedProject): void {
      window.open(project.gitlab_diff_url, '_blank');
    }

    private async loadOutdatedProjects() {
      const response = await this.$http.get(this.$sfRouter.generate('app_api_project_outdated'));
      this.outdatedProjects = response.data;
      this.outdatedProjects!.forEach((p) => {
        Vue.set(this.creatingMrs, p.project.id, false);
      });
      this.rows = this.outdatedProjects!.length;
    }

    protected get fields(): BvTableFieldArray {
      return [
        {
          key: 'project.name',
          label: this.$translator.trans('project.field.name'),
          sortable: true,
          class: 'project-name',
        }, {
          key: 'master_sha',
          label: this.$translator.trans('outdated-project.field.master-sha'),
          class: 'project-sha',
        }, {
          key: 'production_sha',
          label: this.$translator.trans('outdated-project.field.production-sha'),
          class: 'project-sha',
        }, {
          key: '_actions',
          label: this.$translator.trans('general.actions'),
          class: 'project-action',
        },
      ];
    }

    protected get isBusy(): boolean {
      return this.refreshing || this.creatingAllMrs || this.isResyncing;
    }

    protected get isResyncing(): boolean {
      return Object.values(this.creatingMrs).some((v) => v);
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

      td.project-sha {
        font-family: monospace;
      }

      @include media-breakpoint-up(md) {
        .project-sha {
          white-space: nowrap;
          width: 1px;
          padding-left: map_get($spacers, 3);
          padding-right: map_get($spacers, 5);
        }

        .project-action {
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
