<template>
  <div id="app">
    <img alt="Vue logo" src="./assets/logo.png">
    <QueryEditor/>
    <QueryPreview/>
  </div>
</template>

<script>
import QueryPreview from './components/QueryPreview'
import QueryEditor from './components/QueryEditor'

export default {
  name: 'App',
  components: {
    QueryPreview,
    QueryEditor
  }
}
</script>

<style lang="scss">
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin-top: 60px;
}
</style>
