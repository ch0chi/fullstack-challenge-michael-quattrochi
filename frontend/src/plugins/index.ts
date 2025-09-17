import vuetify from "./vuetify";
import pinia from "../stores";
import router from "../router";
import api from "@/plugins/api";

// Register all Vue plugins in one place for better maintainability
export function registerPlugins(app) {
  app
    .use(vuetify)
    .use(router)
    .use(pinia)
    .use(api);
}
