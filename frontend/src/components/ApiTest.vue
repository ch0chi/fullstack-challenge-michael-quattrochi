<script>
export default {
  data: () => ({
    apiResponse: null
  }),

  created() {
    this.fetchData()
  },

  methods: {
    async fetchData() {
      const url = 'http://localhost:8003/api/weather/users/summary'
      this.apiResponse = await (await fetch(url)).json();
    }
  }
}
</script>

<template>
  <div v-if="!apiResponse">
    Pinging the api...
  </div>

  <div v-if="apiResponse">
    The api responded with: <br />
    <code>
    {{ apiResponse }}
    </code>
  </div>
</template>