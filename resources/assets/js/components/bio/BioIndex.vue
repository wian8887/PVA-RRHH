<template>
  <v-container fluid>
    <v-toolbar>
      <v-toolbar-title>Contratos Eventuales</v-toolbar-title>
      <v-tooltip color="white" role="button" bottom>
      <v-icon slot="activator" class="ml-4">info</v-icon>
      <v-btn color="success" @click="getContracts(true)">Sincronizar</v-btn>
    </v-tooltip>
      <v-spacer></v-spacer>
      
      <v-divider
        class="mx-2"
        inset
        vertical
      ></v-divider>
      <v-toolbar-title>
        <v-text-field
            v-model="search"
            append-icon="search"
            label="Buscar"
            clearable
            single-line
            hide-details
            width="20px"
          ></v-text-field>
      </v-toolbar-title>
      <v-divider
        class="mx-2"
        inset
        vertical
      ></v-divider>
      
      <RemoveItem :bus="bus"/>
    </v-toolbar>
    <div v-if="loading">
      <Loading/>
    </div>
    <v-data-table
      v-else
      :headers="headers"
      :items="bio"
      :search="search"
      :rows-per-page-items="[10,20,30,{text:'TODO',value:-1}]"
      disable-initial-sort
      class="elevation-1">
      <template slot="items" slot-scope="props">
        <tr :class="checkEnd(props.item) == 'error' ? `white--text ${color=checkEnd(props.item)}` : checkEnd(props.item)">
          <td :class="(checkEnd(props.item) != '' ? 'bordered' : '') + withoutBorders" class="text-xs-center" @click="props.expanded = !props.expanded"> {{ props.item[0] }} </td>
          <td :class="(checkEnd(props.item) != '' ? 'bordered' : '') + withoutBorders" class="text-xs-left pl-2" @click="props.expanded = !props.expanded"> {{ props.item[1] }} </td>
          <td :class="(checkEnd(props.item) != '' ? 'bordered' : '') + withoutBorders" class="text-xs-left pl-2" @click="props.expanded = !props.expanded"> {{ props.item[2] }}</td>
          <td :class="(checkEnd(props.item) != '' ? 'bordered' : '') + withoutBorders" class="text-xs-center" @click="props.expanded = !props.expanded"> {{ props.item[3] }} </td>
          
        </tr>
      </template>
      <template slot="expand" slot-scope="props">
        <v-card flat>
          <v-card-text>
            <v-list>
              <v-list-tile-content><p><strong>Cargo: </strong>{{ props.item }}</p></v-list-tile-content>
              
            </v-list>
          </v-card-text>
        </v-card>
      </template>
      <v-alert slot="no-results" :value="true" color="error">
        La búsqueda de "{{ search }}" no encontró resultados.
      </v-alert>
    </v-data-table>
  </v-container>
</template>
<script type="text/javascript">
import Vue from "vue";
import RemoveItem from "../RemoveItem";
import Loading from "../Loading";
import { admin, rrhh, juridica } from "../../menu.js";
export default {
  name: "ContractIndex",
  components: {
    RemoveItem,
    Loading
  },
  data() {
    return {
      withoutBorders: ' ml-0 mr-0 pl-0 pr-0',
      loading: true,
      active: true,
      bus: new Vue(),
      headers: [
        {
          text: "ID usuario",
          value: "employee.identity_card",
          align: "center",
          class: ["ml-0", "mr-0", "pl-0", "pr-0"],
        }, {
          text: "Codigo de usuario",
          value: "employee.last_name",
          align: "center",
          class: ["ml-0", "mr-0", "pl-0", "pr-0"],
        }, {
          text: "tipo de usuario",
          value: "position.name",
          align: "center",
          class: ["ml-0", "mr-0", "pl-0", "pr-0"],
        }, {
          text: "Marcado",
          value: "employee.mothers_last_name",
          align: "center",
          class: ["ml-0", "mr-0", "pl-0", "pr-0"],
          sortable: false
        }, 
      ],
      contracts: [],
      bio: [],
      search: "",
      switch1: true,
      contracState: "vigentes",
    }
  },
  computed: {
    endDate() {
      return this.$moment(this.$store.getters.dateNow).endOf('month')
    },
    dateNow() {
      return this.$moment(this.$store.getters.dateNow)
    },
    formTitle() {
      return this.selectedIndex === -1 ? "Nuevo contrato" : "Editar contrato";
    }
  },
  mounted() {
    console.log('biooooo')
    if (!this.$store.getters.options.includes("edit")) {
      this.headers = this.headers
        .filter(el => {
          return el.text != "Acciones";
        });
    }
    this.getContracts(this.active);
    this.bus.$on("closeDialog", () => {
      this.getContracts(this.active);
    });
  },
  methods: {
    async getContracts(active = this.active) {
      try {
        this.active = active;
        let res = await axios.get(`/contract`)
        this.contracts = res.data

        //

        let res2 = await axios.get(`/bio`)
        this.bio = res2.data[0]
        console.log(res2.data)
        if (active) {
          this.contracts = this.contracts.filter(obj => {
            return obj.active === true;
          });
        }
        this.loading = false
      } catch (e) {
        console.log(e);
      }
    },
    editItem(item, mode) {
      this.bus.$emit("openDialog", $.extend({}, item, { mode: mode }));
    },
    async removeItem(item) {
      let payroll = await axios.get(
        "/payroll/getpayrollcontract/" + item.id
      );
      if (payroll.data) {
        alert(
          "No se puede eliminar. Porque este contrato ya se encuentra en PLANILLAS"
        );
      } else {
        this.bus.$emit("openDialogRemove", `/contract/${item.id}`);
      }
    },
    fullName(employee) {
      let names = `${employee.last_name || ""} ${employee.mothers_last_name ||
        ""} ${employee.surname_husband || ""} ${employee.first_name ||
        ""} ${employee.second_name || ""} `;
      names = names
        .replace(/\s+/gi, " ")
        .trim()
        .toUpperCase();
      return names;
    },
    checkEnd(contract) {
      if (
        contract.retirement_date != null &&
        this.endDate.isSame(this.$moment(contract.retirement_date), 'year') &&
        this.endDate.isSame(this.$moment(contract.retirement_date), 'month') &&
        !this.active
      ) {
        return "danger";
      } else if (
        contract.end_date != null &&
        this.endDate.isSame(this.$moment(contract.end_date), 'year') &&
        this.endDate.isSame(this.$moment(contract.end_date), 'month') &&
        !this.active
      ) {
        return "warning";
      } else if (
        this.endDate.isSameOrAfter(this.$moment(contract.end_date)) &&
        this.active) {
        return "error";
      } else {
        return "";
      }
    },
    checkEdit(item) {
      if (item.active == false) {
        if (this.$store.getters.currentUser.roles[0].name == 'admin') {
          return true;
        } else {
          return false;
        }
      } else {
        return true;
      }
    },
    async print(item, type) {
      try {
        let res = await axios({
          method: "GET",
          url: `/contract/print/${item.id}/${type}`,
          responseType: "arraybuffer"
        });
        let blob = new Blob([res.data], {
          type: "application/pdf"
        });
        printJS(window.URL.createObjectURL(blob));
      } catch (e) {
        console.log(e);
      }
    }
  }
};
</script>

<style>
.bordered {
  border-bottom: 0.2pt solid #212121;
}
</style>
