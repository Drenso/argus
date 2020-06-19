<template>
  <div class="mb-2">
    <div class="font-weight-bold">{{ title }}</div>
    <b-progress :max="stats.all" show-value height="2rem">
      <b-progress-bar :value="stats.fully_handled" variant="success"/>
      <b-progress-bar :value="stats.partially_handled" variant="warning"/>
      <b-progress-bar :value="stats.unhandled" variant="danger"/>
    </b-progress>
  </div>
</template>

<script lang="ts">
  import {Component, Prop, Vue} from 'vue-property-decorator';
  import {EventStats as EventStatsObj} from '../../api/EventTypes';

  @Component
  export default class EventStats extends Vue {
    @Prop({required: true, type: String})
    public readonly title!: string;

    @Prop({required: true, type: Object})
    public readonly stats!: EventStatsObj;
  }
</script>
