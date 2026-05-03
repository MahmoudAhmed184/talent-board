import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import { prepareRealtimeAuth } from './realtime/echo'
import { installAuthSession } from './features/auth/composables/useAuth'
import { router } from './router'
import { pinia } from './stores'

const app = createApp(App)

app.use(pinia)
installAuthSession(router)
void prepareRealtimeAuth().catch(() => undefined)
app.use(router)
app.mount('#app')
