<template>
  <div>
    <b-card header-bg-variant="primary" header-text-variant="white">
      <template #header>
        <div class="d-flex">
          <div class="flex-fill">
            {{ 'event.title.stats'|trans }}
          </div>
          <div class="ml-2">
            <a @click="reloadStats">
              <font-awesome-icon icon="sync-alt" fixed-width :spin="refreshStats"/>
            </a>
          </div>
        </div>
      </template>

      <div class="text-center" v-if="stats === null">
        <font-awesome-icon icon="circle-notch" fixed-width spin/>
      </div>
      <div v-else class="mb-n2">
        <b-progress max="3" show-value class="mb-2">
          <b-progress-bar value="1" variant="success" :label="'event.info.fully-handled'|trans"/>
          <b-progress-bar value="1" variant="warning" :label="'event.info.partially-handled'|trans"/>
          <b-progress-bar value="1" variant="danger" :label="'event.info.unhandled'|trans"/>
        </b-progress>

        <EventStats :stats="stats.last_hour" :title="'event.title.last-hour'|trans"/>
        <EventStats :stats="stats.last_day" :title="'event.title.last-day'|trans"/>
        <EventStats :stats="stats.last_month" :title="'event.title.last-month'|trans"/>
        <EventStats :stats="stats.last_year" :title="'event.title.last-year'|trans"/>
      </div>
    </b-card>
  </div>
</template>

<script lang="ts">
  import {Component, Vue} from 'vue-property-decorator';
  import {TimedEventStats} from '../../api/EventTypes';
  import EventStats from './EventStats.vue';

  @Component({
    components: {EventStats},
  })
  export default class DashboardPage extends Vue {
    public refreshStats: boolean = false;
    public stats: TimedEventStats | null = null;

    public mounted() {
      this.loadLatestEvents();
    }

    protected async reloadStats() {
      if (this.refreshStats) {
        return;
      }

      this.refreshStats = true;
      try {
        await this.loadLatestEvents();
      } finally {
        this.refreshStats = false;
      }
    }

    private async loadLatestEvents() {
      const response = await this.$http.get(this.$sfRouter.generate('app_api_event_stats'));
      this.stats = response.data;
    }
  }
</script>
