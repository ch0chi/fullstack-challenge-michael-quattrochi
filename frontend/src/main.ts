/**
 * todo For the frontend, I would have added types and followed a stricter linting policy.
 *      But, to save time, I kept it simple.
 */

import App from "./App.vue";
import { createApp } from "vue";
import { registerPlugins} from "@/plugins";
import "./assets/main.css";

const app = createApp(App);

registerPlugins(app);

app.mount("#app");
