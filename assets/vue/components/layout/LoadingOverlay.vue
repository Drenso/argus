<template>
  <b-overlay :show="show">
    <template #overlay>
      <LoadingOverlayIcon/>
    </template>
    <slot></slot>
  </b-overlay>
</template>

<script lang="ts">
  import {Component, Prop, Vue} from 'vue-property-decorator';
  import LoadingOverlayIcon from './LoadingOverlayIcon.vue';

  @Component({
    components: {LoadingOverlayIcon},
  })
  export default class LoadingOverlay extends Vue {
    @Prop({required: true, type: Boolean})
    public readonly show!: boolean;
  }
</script>
