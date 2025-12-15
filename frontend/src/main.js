import './assets/main.css'
import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

/* --- FONT AWESOME SETUP --- */
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

// Šeit importējam konkrētas ikonas, ko lietosim
import { 
    faUser, faSignOutAlt, faSearch, faPenToSquare, 
    faMapMarkerAlt, faEuroSign, faBriefcase, faPlusCircle, 
    faBullhorn, faHandshake, faCheckCircle, faUserCog, faHammer 
} from '@fortawesome/free-solid-svg-icons'

// Pievienojam tās bibliotēkai
library.add(
    faUser, faSignOutAlt, faSearch, faPenToSquare, 
    faMapMarkerAlt, faEuroSign, faBriefcase, faPlusCircle, 
    faBullhorn, faHandshake, faCheckCircle, faUserCog, faHammer
)

const app = createApp(App)

// Reģistrējam komponenti globāli (lai nav jāimportē katrā failā)
app.component('font-awesome-icon', FontAwesomeIcon)

app.use(router)
app.mount('#app')