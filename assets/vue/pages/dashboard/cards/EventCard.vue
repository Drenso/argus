<template>
  <b-card
      header-bg-variant="primary"
      header-text-variant="white">
    <template #header>
      <div class="d-flex">
        <div class="flex-fill">
          {{ 'event.title.stats'|trans }}
        </div>
        <div class="ml-2">
          <a
              v-if="stats !== null"
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
        v-if="stats === null"
        class="text-center">
      <LoadingOverlayIcon/>
    </div>
    <div v-else>
      <LoadingOverlay :show="refreshing">
        <b-tabs
            content-class="my-2"
            justified>
          <EventStats
              :stats="stats.last_hour"
              :title="'event.title.last-hour'|trans"/>
          <EventStats
              :stats="stats.last_day"
              :title="'event.title.last-day'|trans"/>
          <EventStats
              :stats="stats.last_week"
              :title="'event.title.last-week'|trans"/>
          <EventStats
              :stats="stats.last_month"
              :title="'event.title.last-month'|trans"/>
          <EventStats
              :stats="stats.last_year"
              :title="'event.title.last-year'|trans"/>
        </b-tabs>

        <b-progress
            max="3"
            show-value>
          <b-progress-bar
              :label="'event.info.fully-handled'|trans"
              value="1"
              variant="success"/>
          <b-progress-bar
              :label="'event.info.partially-handled'|trans"
              value="1"
              variant="warning"/>
          <b-progress-bar
              :label="'event.info.unhandled'|trans"
              value="1"
              variant="danger"/>
        </b-progress>
      </LoadingOverlay>
    </div>
  </b-card>
</template>

<script lang="ts">
  import {AxiosResponse} from 'axios';
  import {Component, Vue} from 'vue-property-decorator';
  import {TimedEventStats} from '../../../api/EventTypes';
  import LoadingOverlay from '../../../components/layout/LoadingOverlay.vue';
  import LoadingOverlayIcon from '../../../components/layout/LoadingOverlayIcon.vue';
  import EventStats from '../components/EventStats.vue';

  @Component({
    components: {LoadingOverlay, LoadingOverlayIcon, EventStats},
  })
  export default class EventCard extends Vue {
    protected refreshing = false;
    protected stats: TimedEventStats | null = null;

    public mounted(): void {
      this.loadLatestEvents();
    }

    protected async refresh(): Promise<void> {
      if (this.refreshing) {
        return;
      }

      this.refreshing = true;
      try {
        await this.loadLatestEvents();
      } finally {
        this.refreshing = false;
      }
    }

    private async loadLatestEvents(): Promise<void> {
      const response: AxiosResponse<TimedEventStats> =
          await this.$http.get(this.$sfRouter.generate('app_api_event_stats'));
      this.stats = response.data;
    }
  }
</script>
