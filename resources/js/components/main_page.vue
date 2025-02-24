<template>
  <div class="container border">
    <div class="row justify-content-center">
      <div class="d-inline col-md-3 border"><h3>Фильтр!</h3></div>
      <div class="d-inline col-md-8 border"><h3>Все ближайшие покатушки</h3>
        <pre>{{ data }}</pre>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  name: 'main_page',
  data() {
    return {
      data: null,
    }
  },
  created: function () {
    this.getForm();
  },
  methods: {
    getForm: function () {
      let args = {
        "name": 'test',
        "description": 'Description My travel',
        "country_id": '9',
        "visible_kind": '1',
        "status": '1',
        "travel_type_id": 1
      };
      axios.post('/api/travel/create', args).then(response => {
          this.data = response.data;
          console.log(this.data);
        }
      );
      axios.post('/api/travel/list').then(response => {
          this.data = response.data;
          console.log(this.data);
        }
      );
    },
  },
}
</script>
<style scoped>

</style>
