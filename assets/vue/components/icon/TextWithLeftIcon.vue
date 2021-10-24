<template>
  <div class="d-flex">
    <div class="flex-shrink-0 flex-grow-0 mr-1">
      <slot name="icon">
        <font-awesome-icon
            fixed-width
            :icon="icon"/>
      </slot>
    </div>
    <div class="flex-fill">
      <slot/>
    </div>
  </div>
</template>

<script lang="ts">
  import {Component, Prop, Vue} from 'vue-property-decorator';

  @Component
  export default class TextWithLeftIcon extends Vue {
    @Prop({required: true})
    public readonly icon!: string | string[];
  }
</script>
