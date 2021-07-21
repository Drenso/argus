<template>
  <div>
    <ProjectCard/>
    <OutdatedProjectsCard/>
    <EventCard/>
  </div>
</template>

<script lang="ts">
  import {Component, Vue} from 'vue-property-decorator';
  import EventCard from './cards/EventCard.vue';
  import OutdatedProjectsCard from './cards/OutdatedProjectsCard.vue';
  import ProjectCard from './cards/ProjectCard.vue';

  @Component({
    components: {OutdatedProjectsCard, ProjectCard, EventCard},
  })
  export default class DashboardPage extends Vue {

  }
</script>
