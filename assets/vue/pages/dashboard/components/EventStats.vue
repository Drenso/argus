<template>
  <b-tab :title="title">
    <template v-if="stats">
      <b-progress
          v-show="stats.all > 0"
          height="2rem"
          :max="stats.all"
          show-value>
        <b-progress-bar
            :value="stats.fully_handled"
            variant="success"/>
        <b-progress-bar
            :value="stats.partially_handled"
            variant="warning"/>
        <b-progress-bar
            :value="stats.unhandled"
            variant="danger"/>
      </b-progress>
      <div
          v-show="stats.all === 0"
          style="height: 2rem;">
        <div class="d-flex align-items-center h-100">
          <span>{{ 'event.info.none-recorded'|trans }}</span>
        </div>
      </div>
    </template>
  </b-tab>
</template>

<script lang="ts">
  import {Component, Prop, Vue} from 'vue-property-decorator';
  import {EventStats as EventStatsObj} from '../../../api/EventTypes';

  @Component
  export default class EventStats extends Vue {
    @Prop({required: true, type: String})
    public readonly title!: string;

    @Prop({required: true, type: Object})
    public readonly stats!: EventStatsObj;
  }
</script>
