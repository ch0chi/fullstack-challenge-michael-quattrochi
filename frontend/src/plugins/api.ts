import axios from "axios";
import _ from "lodash";

/**
 * todo If I had more time, I would use use JWT tokens with refresh tokens for authentication using laravel sanctum.
 *      But, to save time, I kept it simple.
 * API plugin to handle all API requests
 */
class Api {
  axios;

  constructor() {
    this.axios = axios.create();
  }

  /**
   * Make the plugin globally available
   * @param app
   * @param options
   */
  install(app, options) {
    app.provide("api", this);
  }

  logError(error) {
    let data = [];
    if (error.response) {
      data.push(
        "The request was made and the server responded with a status code that falls out of the range of 2xx"
      );
      data.push(error.response.data);
      data.push(error.response.status);
      data.push(error.response.headers);
    } else if (error.request) {
      data.push("The request was made but no response was received");
      // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
      // http.ClientRequest in node.js
      data.push(error.request);
    } else {
      // Something happened in setting up the request that triggered an Error
      data.push("Error");
      data.push(error.message);
      if (error.stack) {
        data.push(error.stack);
      }
    }
    let eObj = {};
    try {
      eObj = error.toJSON();
    } catch (e) {
      eObj = JSON.parse(JSON.stringify(error));
    }
    data.push(eObj);
    console.error(data);
  }

  parseUrl(url, params) {
    let newUrl = url;
    let newParams = {};
    _.each(params, (val, key) => {
      let re = new RegExp("{" + key + "}", "g");
      if (re.test(newUrl)) {
        newUrl = newUrl.replace(re, val);
      } else {
        newParams[key] = val;
      }
    });
    return {
      url: newUrl,
      params: newParams,
    };
  }

  requestOptions(method, uri, params = {}, options = {}) {
    method = method.toLowerCase();
    let urlObj = this.parseUrl(uri, params);
    options.method = method;
    options.url = urlObj.url;
    if (!_.isEmpty(urlObj.params)) {
      if (method === "get") {
        options.params = urlObj.params;
      } else {
        options.data = urlObj.params;
      }
    }
    return options;
  }

  request(method, uri, params = {}, options = {}) {
    return this.axios(this.requestOptions(method, uri, params, options));
  }

  get(uri, params = {}, options = {}) {
    return this.request("get", uri, params, options);
  }

  post(uri, params = {}, options = {}) {
    return this.request("post", uri, params, options);
  }

  put(uri, params = {}, options = {}) {
    return this.request("put", uri, params, options);
  }

  patch(uri, params = {}, options = {}) {
    if (!options.headers) {
      options.headers = {};
    }
    if (!options.headers["Content-Type"]) {
      options.headers["Content-Type"] = "application/merge-patch+json";
    }
    return this.request("patch", uri, params, options);
  }

  delete(uri, params = {}, options = {}) {
    return this.request("delete", uri, params, options);
  }
}

const api = new Api();

export default api;
