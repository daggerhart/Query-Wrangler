<template>
    <div class="tax-query__wrapper">
        <select v-bind="relation">
            <option value="AND">AND</option>
            <option value="OR">OR</option>
        </select>
        <div v-for="(query, key) in queries" :key="key">
            {{ key }}
            <div>
                Taxonomy:
                <input type="text" name="taxonomy" @input="update(queries[key], 'taxonomy', $event)">
            </div>
            <div>
                Field:
                <input type="text" name="field" @input="update(queries[key], 'field', $event)">
            </div>
            <div>
                Terms:
                <input type="text" name="terms" @input="update(queries[key], 'terms', $event)">
            </div>
            <div>
                Include Children:
                <input type="text" name="include_children" @input="update(queries[key], 'include_children', $event)">
            </div>
            <div>
                Operator:
                <input type="text" name="operator" @input="update(queries[key], 'operator', $event)">
            </div>
        </div>
        <div>
            {{ JSON.stringify(queries) }}
        </div>
    </div>
</template>

<script>

  function createGroup(name) {
    return {
      [name]: {
        taxonomy: '',
        field: '',
        terms: [],
        include_children: true,
        operator: 'IN',
      }
    }
  }
  export default {
    name: 'TaxQuery',
    props: {
      depth: {
        type: Number,
        default: 0,
      },
      relation: String,
      queries: {
        type: Object,
        default: function () {
          return createGroup('tax_query_0');
        }
      },
    },
    methods: {
      update: function (obj, prop, event) {
        obj[prop] = event.target.value
      },
    },
  }
</script>
