import "./bootstrap";
import Alpine from "alpinejs";
Alpine.data("shippingForm", (initial = {}) => ({
  origin: "",
  destination: "",
  originCountry: "",
  destinationCountry: "",
  weight: "",
  dimensions: "",
  service: "standard",
  rates: {},
  unavailable: {},
  loading: false,
  error: "",
  controller: null,
  fingerprint: "",
  ...initial,
  init() {
    if (
      ![
        "standard",
        "express",
        "freight",
        "international",
      ].includes(this.service)
    )
      this.service = "standard";
    this.refresh();
  },
  get estimate() {
    return this.rates[this.service] || null;
  },
  money(value) {
    return new Intl.NumberFormat("en-US", {
      style: "currency",
      currency: "USD",
    }).format(value);
  },
  date(value) {
    return new Date(value + "T12:00:00").toLocaleDateString("en-US", {
      month: "short",
      day: "numeric",
      year: "numeric",
    });
  },
  async refresh() {
    const data = {
      origin_country: this.originCountry.trim(),
      destination_country: this.destinationCountry.trim(),
      weight: this.weight,
      dimensions: this.dimensions,
    };
    const fingerprint = JSON.stringify(data);
    if (fingerprint === this.fingerprint) return;
    this.fingerprint = fingerprint;
    this.controller?.abort();
    this.rates = {};
    this.unavailable = {};
    this.error = "";
    this.loading = false;
    if (
      !data.origin_country ||
      !data.destination_country ||
      !(Number(data.weight) > 0)
    )
      return;
    this.controller = new AbortController();
    const signal = this.controller.signal;
    this.loading = true;
    try {
      const response = await fetch("/rates/estimate", {
        method: "POST",
        signal,
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
            .content,
        },
        body: JSON.stringify(data),
      });
      const result = await response.json();
      if (!response.ok)
        throw new Error(
          Object.values(result.errors || {}).flat()[0] ||
            (response.status === 429
              ? "Too many estimates. Please wait a moment."
              : "Could not calculate an estimate. Please try again."),
        );
      if (signal.aborted) return;
      this.rates = result.rates;
      this.unavailable = result.unavailable;
      if (this.unavailable[this.service]) {
        this.service = "express";
        this.error =
          "That service is unavailable. Express is selected for this route.";
      }
    } catch (error) {
      if (error.name !== "AbortError") this.error = error.message;
    } finally {
      if (!signal.aborted) this.loading = false;
    }
  },
}));
window.Alpine = Alpine;
Alpine.start();
