<template>
  <b-card
      header-bg-variant="primary"
      header-text-variant="white">
    <template #header>
      <div class="d-flex">
        <div class="flex-fill">
          {{ 'outdated-project.title._'|trans }}
        </div>
        <div class="ml-2">
          <a
              v-if="outdatedProjects !== null"
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
        v-if="outdatedProjects === null"
        class="text-center">
      <LoadingOverlayIcon/>
    </div>
    <div v-else>
      <LoadingOverlay :show="refreshing">
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
            :items="outdatedProjects"
            :per-page="perPage"
            show-empty
            small
            sort-by="project.name"
            stacked="md"
            @filtered="onFiltered">
          <template #cell(_actions)="row">
            <a
                v-b-tooltip.hover.topleft
                class="pointer text-secondary"
                :href="row.item.gitlab_diff_url"
                target="_blank"
                :title="'outdated-project.button.show-diff'|trans">
              <font-awesome-icon icon="search"/>
            </a>

            <a
                v-b-tooltip.hover.topleft
                class="pointer text-primary"
                :title="'outdated-project.button.create-mr'|trans"
                @click="createMr(row.item)">
              <font-awesome-icon
                  fixed-width
                  :icon="creatingMrs[row.item.project.id] ? 'circle-notch' : 'play'"
                  :spin="creatingMrs[row.item.project.id]"/>
            </a>
          </template>
        </b-table>

        <div
            v-if="outdatedProjects.length > 1"
            class="text-right">
          <b-button
              :disabled="isBusy"
              variant="success"
              @click="createMrs">
            <font-awesome-icon
                fixed-width
                :icon="creatingAllMrs ? 'circle-notch' : 'play'"
                :spin="creatingAllMrs"/>
            {{ 'outdated-project.button.create-mrs'|trans }}
          </b-button>
        </div>
      </LoadingOverlay>
    </div>
  </b-card>
</template>

<script lang="ts">
  import {AxiosResponse} from 'axios';
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
    protected refreshing = false;
    protected outdatedProjects: OutdatedProject[] | null = null;

    protected filter = '';
    protected rows = 0;
    protected perPage = 5;
    protected currentPage = 1;

    protected creatingAllMrs = false;
    protected creatingMrs: { [projectId: number]: boolean } = {};

    public mounted(): void {
      this.loadOutdatedProjects();
    }

    protected async createMr(item: OutdatedProject): Promise<void> {
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

    protected async createMrs(): Promise<void> {
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

    protected onFiltered(filteredItems: OutdatedProject[]): void {
      this.rows = filteredItems.length;
      this.currentPage = 1;
    }

    protected async refresh(): Promise<void> {
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

    private async loadOutdatedProjects(): Promise<void> {
      const response: AxiosResponse<OutdatedProject[]> =
          await this.$http.get(this.$sfRouter.generate('app_api_project_outdated'));
      this.outdatedProjects = response.data;
      this.outdatedProjects?.forEach((p) => {
        Vue.set(this.creatingMrs, p.project.id, false);
      });
      this.rows = this.outdatedProjects?.length ?? 0;
    }

    protected get fields(): BvTableFieldArray {
      const fields: BvTableFieldArray = [
        {
          key: 'project.name',
          label: this.$translator.trans('project.field.name'),
          sortable: true,
          class: 'project-name',
        },
      ];


      if (window.HAS_MULTIPLE_HOSTS) {
        fields.push({
          key: 'project.host',
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
      }]);

      return fields;
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
