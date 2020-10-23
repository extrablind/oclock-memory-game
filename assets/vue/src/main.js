import Vue from 'vue'
import App from './App'

Vue.config.performance = true

// Declare a new vue app with our... App component
new Vue({
    el: '#app',
    template: '<App/>',
    components: {
        App
    },
})