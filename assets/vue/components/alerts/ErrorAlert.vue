<template>
  <ArgusAlert
      v-bind="$attrs"
      icon="times"
      variant="danger"
      v-on="$listeners">
    <template #text>
      <slot>{{ text }}</slot>
    </template>
  </ArgusAlert>
</template>

<script lang="ts">
  import {Component, Prop, Vue} from 'vue-property-decorator';
  import ArgusAlert from './ArgusAlert.vue';

  @Component({
    components: {ArgusAlert},
  })
  export default class ErrorAlert extends Vue {
    @Prop({type: String})
    public readonly text!: string | undefined;
  }
</script>
