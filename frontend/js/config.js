const CONFIG = {
    API_BASE_URL: window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1'
        ? '../backend-php/api/'
        : 'https://your-backend-service.onrender.com/api/' 
};
