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
      <font-awesome-icon icon="circle-notch" fixed-width spin/>
    </div>
    <div v-else>
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
          sort-by="last_event" sort-null-last
          :filter="filter"
          :fields="fields" :items="projects" :per-page="perPage" :current-page="currentPage"
          @filtered="onFiltered"/>
    </div>
  </b-card>
</template>

<script lang="ts">
  import {BvTableFieldArray} from 'bootstrap-vue';
  import {Component, Vue} from 'vue-property-decorator';
  import {Project} from '../../../api/ProjectTypes';

  @Component
  export default class ProjectCard extends Vue {
    public refreshing: boolean = false;
    public projects: Project[] | null = null;

    public filter: string = '';
    public rows: number = 0;
    public perPage: number = 10;
    public currentPage: number = 1;

    public mounted() {
      this.loadProjects();
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
