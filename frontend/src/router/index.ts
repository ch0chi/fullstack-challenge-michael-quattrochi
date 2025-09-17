import { createRouter, createWebHistory } from "vue-router";
import HomeView from "../views/HomeView.vue";

/*
  todo For simplicity, I kept the router with a single route.
        But, if I had more time, I would have added more routes and nested routes.
        Also, I would have added route guards for authentication.
 */
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: "/",
      name: "home",
      component: HomeView,
    },
  ],
});

export default router;
