# WeatherWise API 🚀

This is the **Laravel API backend** powering the WeatherWise frontend. It serves weather data from the OpenWeatherMap API and formats it for seamless frontend consumption.

---

## ✨ Features

* 🌤️ Current weather data endpoint
* 📈 5-day forecast endpoint
* 🧠 (Coming soon) Gemini AI integration for activity suggestions
* 📊 Clean, structured JSON responses formatted for the WeatherWise frontend

---

## 🚀 Getting Started

### Prerequisites

* PHP 8.2+
* Composer
* Laravel 11+
* OpenWeatherMap API key

### 📦 Installation

```bash
git clone https://github.com/Morg3an/weather-wise-api.git
cd weather-wise-api
composer install
cp .env.example .env
php artisan key:generate
```

### 🚫 Configure .env

Update your `.env` with:

```env
OPENWEATHERMAP_API_KEY=your_openweathermap_api_key
FRONTEND_URL=http://localhost:3000  # CORS
```

### 🔧 Run the Server

```bash
php artisan serve
```

Your API will be available at [http://localhost:8000](http://localhost:8000).

---

## 🔄 Stay Tuned

Upcoming improvements:

* Gemini-powered activity suggestion endpoint
* Rate limiting
* Unit tests and API documentation

---

## 🤝 Collaboration

Contributions are welcome!

1. Fork this repository
2. Create your feature branch (`git checkout -b feature/your-feature`)
3. Commit your changes
4. Push to the branch
5. Create a new pull request

---

## ⚖️ License

This project is licensed under the **MIT License**. See the [LICENSE](LICENSE) file for details.
