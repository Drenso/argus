<template>
  <div class="text-center">
    <font-awesome-icon
        icon="circle-notch"
        size="4x"
        spin/>
  </div>
</template>

<script lang="ts">
  import {Component, Vue} from 'vue-property-decorator';

  @Component
  export default class LoadingOverlay extends Vue {

  }
</script>
