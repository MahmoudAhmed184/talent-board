import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import { http } from '../http'

type EchoInstance = InstanceType<typeof Echo>

declare global {
  interface Window {
    Echo?: EchoInstance
    Pusher: typeof Pusher
  }
}

let echo: EchoInstance | null = null

function numberEnv(value: unknown, fallback: number): number {
  const parsed = Number(value)
  return Number.isFinite(parsed) ? parsed : fallback
}

function resolveRealtimeHost(): string {
  const configuredHost = import.meta.env.VITE_REVERB_HOST

  if (configuredHost && configuredHost !== 'auto') {
    return configuredHost
  }

  return window.location.hostname
}

export async function prepareRealtimeAuth(): Promise<void> {
  await http.get('/sanctum/csrf-cookie', {
    skipUnauthorizedHandler: true,
  })
}

export function getEcho(): EchoInstance {
  if (echo) {
    return echo
  }

  window.Pusher = Pusher

  const scheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http'

  echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY ?? '',
    wsHost: resolveRealtimeHost(),
    wsPort: numberEnv(import.meta.env.VITE_REVERB_PORT, 8080),
    wssPort: numberEnv(import.meta.env.VITE_REVERB_PORT, 8080),
    forceTLS: scheme === 'https',
    enabledTransports: ['ws', 'wss'],
    authorizer: (channel: { name: string }) => ({
      authorize: (socketId: string, callback: (error: Error | null, data?: unknown) => void) => {
        http
          .post('/broadcasting/auth', {
            socket_id: socketId,
            channel_name: channel.name,
          })
          .then((response) => callback(null, response.data))
          .catch((error) => callback(error))
      },
    }),
  } as never)

  window.Echo = echo

  return echo
}

export function disconnectEcho(): void {
  echo?.disconnect()
  echo = null
  window.Echo = undefined
}
