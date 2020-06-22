<template>
  <div>
    <ProjectCard/>
    <EventCard/>
  </div>
</template>

<script lang="ts">
  import {Component, Vue} from 'vue-property-decorator';
  import EventCard from './cards/EventCard.vue';
  import ProjectCard from './cards/ProjectCard.vue';

  @Component({
    components: {ProjectCard, EventCard},
  })
  export default class DashboardPage extends Vue {

  }
</script>
