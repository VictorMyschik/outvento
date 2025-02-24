<template>
  <div>
    <div class="mr-bold mr-middle" v-if="load_data">Загрузка данных <i class="fa fa-spinner fa-spin"></i></div>
    <div v-else class="mr-bold mr-middle">
      <span v-text="'Найдено: '+count_rows"></span>
      <span v-if="selected.length" v-text="', выделено: '+selected.length"></span>
    </div>

    <table class="table table-sm mr-small table-hover table-bordered col-md-12">
      <thead class="mr-bold mr-table-subhead mr-test">
      <tr class="mr-auto-size">
        <th v-for="head in table_header" v-on:click="mr_click_head_field(head)"
            v-bind:class="typeof head['sort'] !== 'undefined' ? 'mr_cursor' : ''">
          <div v-if="head['name'] === '#checkbox'">
            <label><input type='checkbox' @click='checkAll()' v-model='is_check_all'></label>
          </div>
          <div v-else>{{ head['name'] }}
            <i v-if="mr_field === head['sort']" class="mr-color-green-dark"
               :class="[mr_sort === 'asc' ? arrow_down : arrow_up]"></i>
          </div>
        </th>
      </tr>
      </thead>
      <tbody class="mr-middle" v-bind:class="mr_wait ? 'mr_wait_class' : ''">
      <tr v-for="(tr, ind_row) in NewCompileData">
        <td v-for="(td, ind) in tr">
          <div v-if="with_checkbox && ind === 0">
            <label style="width: 100%;" class="mr-cursor">
              <input type='checkbox' v-bind:value='tr[ind]' v-model='selected' @change='updateCheckAll()'></label>
          </div>
          <v-runtime-template v-else :template="td"></v-runtime-template>
        </td>
      </tr>
      </tbody>
    </table>
    <div class="row no-gutters">
      <div class="">
        <pagination :data="table_body" @pagination-change-page="List" :limit="5">
          <span class="" slot="prev-nav">Previous</span>
          <span class="" slot="next-nav">Next</span>
        </pagination>
      </div>
      <div>
        <label class="m-l-15">
          <input class="form-control" type="number" min="0" name="in_page" style="width: 100px;"
                 @input="ChangeColInPage"
                 v-model="per_page"
                 placeholder="in page">
        </label>
      </div>
    </div>
    <div>

    <span v-for="btn in btn_selected" class="m-r-15">
      <button v-text="btn['name']" :class="btn['class']" v-on:click="selected_ids(btn['method'])" :title="btn['title']">
      </button>
    </span>
    </div>
  </div>
</template>

<script>
import VRuntimeTemplate from "v-runtime-template";
import MrPopupForm from "./MrPopupForm";

export default {
  components: {MrPopupForm, VRuntimeTemplate},
  props: ['mr_route', 'mr_object'],

  data() {
    return {
      mr_wait: false, // затемнение при загрузке
      table_body: {},
      table_header: [],
      // Сортировка
      mr_field: 'id',
      mr_sort: 'asc',
      // Стрелки сортировки
      arrow_up: 'fa fa-arrow-up',
      arrow_down: 'fa fa-arrow-down',
      token: '',
      limit: 5, // Макс кол ссылок на другие стр.
      per_page: null,
      count_rows: 0,
      current_page: 1,

      url_param: '',

      btn_selected: [],
      message: '',

      // Checkboxes
      is_check_all: false,
      selected: [],
      load_data: false,
      with_checkbox: false
    }
  },
  computed: {
    NewCompileData() {
      let new_table = [];
      let i = 1;
      for (let tr in this.table_body.data) {
        let row = [];
        for (let td in this.table_body.data[tr]) {
          let data = this.table_body.data[tr][td];
          if (this.with_checkbox && td === '0') {
            row[td] = data;
          } else {
            if (data !== null) {
              row[td] = `<span>` + data + `</span>`;
            } else {
              row[td] = null;
            }
          }
        }

        new_table[i] = row;
        i++;
      }

      return new_table;
    }
  },

  mounted() {
    this.load_data = true;
    this.mr_wait = true;

    if (this.mr_object['is_checkboxes']) {
      this.with_checkbox = true;
    }

    this.List();
  },

  methods: {
    ChangeColInPage: function () {
      this.current_page = 1;
      setTimeout(this.List, 2000);
    },

    List(page) {
      if (page) {
        this.current_page = page;
      }

      const strGET = window.location.search.replace('?', '');

      this.url_param = '&page=' + this.current_page + '&' + 'per_page=' + this.per_page + '&' + 'sort' + '=' + this.mr_sort + '&field=' + this.mr_field;
      if (strGET.length > 2) {
        this.url_param += '&' + strGET;
      }

      this.SendRequest();
    },

    // Отправка запроса
    SendRequest: function (data) {
      this.load_data = true;
      this.mr_wait = true;
      data = this.mr_object.arguments;
      axios.post(this.mr_route + this.url_param, data).then(response => {
            this.table_body = response.data.body;
            this.table_header = response.data.header;

            this.count_rows = response.data.count;
            this.btn_selected = response.data.btn_selected;
            this.token = this.mr_wait = false;
            this.load_data = false;
            this.message = response.data.result;
          }
      );
    },

    mr_click_head_field(head_name) {
      if (typeof (head_name['sort']) != "undefined" && head_name['sort'] !== null) {
        this.mr_field = head_name['sort'];

        if (this.mr_sort === 'asc') {
          this.mr_sort = 'desc';
        } else {
          this.mr_sort = 'asc';
        }

        // Если нету роута - сортировка в пределах имеющегося списка
        if (this.mr_route) {
          this.List();
        }
      }
    },

    checkAll: function () {
      this.is_check_all = !this.is_check_all;
      this.selected = [];
      if (this.is_check_all) {
        for (let key in this.table_body.data) {
          this.selected.push(this.table_body.data[key][0]);
        }
      }

      this.ReturnSelected();
    },

    updateCheckAll: function () {
      this.is_check_all = this.selected.length === this.table_body.length;

      this.ReturnSelected();
    },

    ReturnSelected: function () {
      this.$emit('selected', this.selected);
    },

    doubleClick: function (data) {
      this.selected.push(this.table_body.data[data][0]);
    },

    selected_ids: function (method) {
      this.load_data = true;
      this.mr_wait = true;
      axios.post(this.mr_route + this.url_param + '&method=' + method, {selected: this.selected}).then(response => {
            this.mr_wait = false;
            this.load_data = false;

            this.SendRequest();
            this.selected = [];
            this.is_check_all = false;
            if (response.data['message']) {
              alert(response.data['message']);
            }
          }
      );
    }
  },
}
</script>

<style scoped>
.mr_wait_class {
  background-color: rgba(162, 164, 185, 0.6);
  color: #a2a4b9;
}

.page-link {
  color: red;
  padding: 0 0 0 0;
}

.mr_cursor {
  cursor: pointer;
  color: #0a1041;
}

.mr_cursor:hover {
  background-color: rgba(230, 232, 254, 0.3);
}
</style>