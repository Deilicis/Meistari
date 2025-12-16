import './assets/main.css'
import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

import { 
    faUser, faSignOutAlt, faSearch, faPenToSquare, 
    faMapMarkerAlt, faEuroSign, faBriefcase, faPlusCircle, 
    faBullhorn, faHandshake, faCheckCircle, faUserCog, faHammer,
    faTrash, faEye, faTimes, faBuilding, faArrowLeft, faEnvelope, faLock,
    faComments, faStar, faUserPlus, faPaperPlane, faTrophy, faChevronRight
} from '@fortawesome/free-solid-svg-icons'

library.add(
    faUser, faSignOutAlt, faSearch, faPenToSquare, 
    faMapMarkerAlt, faEuroSign, faBriefcase, faPlusCircle, 
    faBullhorn, faHandshake, faCheckCircle, faUserCog, faHammer,
    faTrash, faEye, faTimes, faBuilding, faArrowLeft, faEnvelope, faLock,
    faComments, faStar, faUserPlus, faPaperPlane, faTrophy, faChevronRight
)

const app = createApp(App)

app.component('font-awesome-icon', FontAwesomeIcon)

app.use(router)
app.mount('#app')