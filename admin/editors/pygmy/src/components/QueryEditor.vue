<template>
    <div class="editor__wrapper">
        <tax-query :depth="0" />
    </div>
</template>

<script>
  import TaxQuery from './FieldTypes/TaxQueryGroup'

  export default {
    name: 'QueryEditor',
    components: {
      TaxQuery,
    },
    props: {
      depth: {
        type: Number,
        default: 0,
      },
      relation: String,
      queries: Object,
    },
    methods: {
      update: function (obj, prop, event) {
        Vue.set(obj, prop, event.target.value)
      }
    },
  }
</script>
