<script setup lang="ts">
/**
 * todo I would normally break this into smaller components if I had more time.
 *      I'd also add types to comply with the TS standards.
 * This component fetches and displays a summary of users' weather data in a table format.
 * It allows users to click on a row to view detailed weather information in a dialog.
 * The component uses Vuetify for UI components and styling.
 */
import { ref, computed, onMounted, inject } from "vue";

const $api = inject("api");
const weatherData = ref([]);
const loading = ref(true);
const error = ref("");
const dialog = ref(false);
const selectedItem = ref(null);

onMounted(() => {
  fetchWeatherData();
});

const headers = ref([
  {
    title: "Name",
    sortable: true,
    key: "name",
  },
  {
    title: "Temperature",
    sortable: true,
    key: "temperature",
  },
  {
    title: "Condition",
    sortable: false,
    key: "condition",
  },
]);

/**
 * todo Ideally, I would add pagination if there were more users, but for this example, I am keeping it simple.
 */
const usersWeatherSummaryRows = computed(() => {
  return weatherData.value.map((item) => ({
    id: item.user.id,
    name: item.user.name,
    latitude: item.user.latitude,
    longitude: item.user.longitude,
    temperature: item.weather ? `${item.weather.temperature} °F` : "N/A",
    condition: item.weather ? item.weather.conditions : "N/A",
  }));
});

const userWeatherDetails = computed(() => {
  if (!selectedItem.value) return null;
  const userData = weatherData.value.find(
    (item) => item.user.id === selectedItem.value.id
  );
  return userData ? userData.weather : null;
});

const openRowDialog = (event, item) => {
  selectedItem.value = item.item;
  dialog.value = true;
};

const fetchWeatherData = async () => {
  loading.value = true;
  error.value = "";
  try {
    const res = await $api.get("/api/weather/users/summary");
    weatherData.value = res.data || [];
  } catch (err) {
    console.log(err); //todo would add better error handling if i had more time
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <v-card class="mx-auto" max-width="800">
    <v-card-title class="d-flex align-center">Weather Summary</v-card-title>
    <v-spacer></v-spacer>
    <v-card-text>
      <v-data-table
        :headers="headers"
        :items="usersWeatherSummaryRows"
        :loading="loading"
        @click:row="openRowDialog"
        item-key="id"
        hover
        class="elevation-1"
      >
        <template #no-data> No weather data available. </template>
        <template #loading> Loading weather data... </template>
      </v-data-table>
    </v-card-text>
  </v-card>
  <!--  todo this should honestly be a child component. I'd also extract the weather details into a child component.-->
  <v-dialog v-model="dialog" max-width="400">
    <v-card>
      <v-card-title> Weather Details </v-card-title>
      <v-card-text>
        <div v-if="selectedItem">
          <p><strong>Name:</strong> {{ selectedItem.name }}</p>
          <p><strong>Latitude:</strong> {{ selectedItem.latitude }}</p>
          <p><strong>Longitude:</strong> {{ selectedItem.longitude }}</p>
          <div v-if="userWeatherDetails">
            <p>
              <strong>Temperature:</strong>
              {{ userWeatherDetails.temperature }} °F
            </p>
            <p>
              <strong>Feels Like:</strong> {{ userWeatherDetails.feels_like }}
            </p>
            <p>
              <strong>Conditions:</strong> {{ userWeatherDetails.conditions }}
            </p>
            <p><strong>Pressure:</strong> {{ userWeatherDetails.pressure }}</p>
            <p><strong>Humidity:</strong> {{ userWeatherDetails.humidity }}%</p>
            <p>
              <strong>Wind Speed:</strong>
              {{ userWeatherDetails.wind_speed }} mph
            </p>
            <p>
              <strong>Wind Direction:</strong>
              {{ userWeatherDetails.wind_direction }}
            </p>
            <p>
              <strong>Wind Gusts:</strong> {{ userWeatherDetails.wind_gust }}
            </p>
            <p>
              <strong>Visibility:</strong> {{ userWeatherDetails.visibility }}
            </p>
            <p>
              <strong>Cloudiness:</strong> {{ userWeatherDetails.cloudiness }}
            </p>
          </div>
        </div>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn text @click="dialog = false">Close</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<style scoped></style>
