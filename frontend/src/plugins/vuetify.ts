/**
 *
 * Framework documentation: https://vuetifyjs.com`
 */

// Styles
//import '@mdi/font/css/materialdesignicons.css'
import "vuetify/styles";

// Composables
import { createVuetify } from "vuetify";
import * as components from "vuetify/components";
import * as directives from "vuetify/directives";

export default createVuetify({
  theme: {
    defaultTheme: "dark",
    themes: {
      dark: {
        colors: {
          primary: "#3F51B5",
          secondary: "#FFC107",
        },
      },
      light: {
        colors: {
          primary: "#1867C0",
          secondary: "#5CBBF6",
        },
      },
    },
  },
  defaults: {
    VBtn: {
      variant: "flat",
    },
    VCard: {
      VBtn: {
        variant: "flat",
      },
    },
    VCardActions: {
      VBtn: {
        variant: "flat",
      },
    },
  },
  components,
  directives,
});
