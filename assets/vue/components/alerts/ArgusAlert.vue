<template>
  <b-alert
      v-bind="$attrs"
      v-on="$listeners">
    <div class="d-flex">
      <div class="flex-grow-0 flex-shrink-0 align-self-center mr-3">
        <font-awesome-icon
            fixed-width
            :icon="icon"/>
      </div>
      <div class="flex-fill">
        <slot name="text">
          {{ text }}
        </slot>
      </div>
    </div>
  </b-alert>
</template>

<script lang="ts">
  import {Component, Prop, Vue} from 'vue-property-decorator';

  @Component
  export default class ArgusAlert extends Vue {
    @Prop({required: true})
    public readonly icon!: string | string[];

    @Prop({type: String})
    public readonly text!: string | undefined;
  }
</script>
