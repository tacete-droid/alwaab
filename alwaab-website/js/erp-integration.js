(function (global) {
  "use strict";

  function getConfig() {
    return global.ALWAAB_ERP || {};
  }

  function submitQuoteRequest(payload) {
    var cfg = getConfig();
    if (!cfg.apiUrl) {
      return Promise.reject(new Error("ERP API URL is not configured"));
    }

    return fetch(cfg.apiUrl, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        "X-Website-Key": cfg.apiKey || "",
      },
      body: JSON.stringify(payload),
    }).then(function (response) {
      return response.json().then(function (data) {
        if (!response.ok) {
          var message = data.message || (data.errors ? Object.values(data.errors).flat().join(" ") : "Request failed");
          throw new Error(message);
        }
        return data;
      });
    });
  }

  global.AlwaabErp = {
    submitQuoteRequest: submitQuoteRequest,
  };
})(window);
