<template>
  <b-card header-bg-variant="primary" header-text-variant="white">
    <template #header>
      <div class="d-flex">
        <div class="flex-fill">
          {{ 'event.title.stats'|trans }}
        </div>
        <div class="ml-2">
          <a @click="refresh" class="pointer" v-if="stats !== null"
             v-b-tooltip.hover.left :title="'general.refresh'|trans">
            <font-awesome-icon icon="sync-alt" fixed-width :spin="refreshing"/>
          </a>
        </div>
      </div>
    </template>

    <div class="text-center" v-if="stats === null">
      <LoadingOverlayIcon/>
    </div>
    <div v-else>
      <LoadingOverlay :show="refreshing">
        <b-tabs content-class="my-2" justified>
          <EventStats :stats="stats.last_hour" :title="'event.title.last-hour'|trans"/>
          <EventStats :stats="stats.last_day" :title="'event.title.last-day'|trans"/>
          <EventStats :stats="stats.last_week" :title="'event.title.last-week'|trans"/>
          <EventStats :stats="stats.last_month" :title="'event.title.last-month'|trans"/>
          <EventStats :stats="stats.last_year" :title="'event.title.last-year'|trans"/>
        </b-tabs>

        <b-progress max="3" show-value>
          <b-progress-bar value="1" variant="success" :label="'event.info.fully-handled'|trans"/>
          <b-progress-bar value="1" variant="warning" :label="'event.info.partially-handled'|trans"/>
          <b-progress-bar value="1" variant="danger" :label="'event.info.unhandled'|trans"/>
        </b-progress>
      </LoadingOverlay>
    </div>
  </b-card>
</template>

<script lang="ts">
  import {Component, Vue} from 'vue-property-decorator';
  import {TimedEventStats} from '../../../api/EventTypes';
  import LoadingOverlay from '../../../components/layout/LoadingOverlay.vue';
  import LoadingOverlayIcon from '../../../components/layout/LoadingOverlayIcon.vue';
  import EventStats from '../components/EventStats.vue';

  @Component({
    components: {LoadingOverlay, LoadingOverlayIcon, EventStats},
  })
  export default class EventCard extends Vue {
    public refreshing: boolean = false;
    public stats: TimedEventStats | null = null;

    public mounted() {
      this.loadLatestEvents();
    }

    protected async refresh() {
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

    private async loadLatestEvents() {
      const response = await this.$http.get(this.$sfRouter.generate('app_api_event_stats'));
      this.stats = response.data;
    }
  }
</script>
