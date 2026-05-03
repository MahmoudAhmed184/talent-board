import { onBeforeUnmount } from 'vue'
import { getEcho, prepareRealtimeAuth } from '../realtime/echo'

type EchoPayload = Record<string, unknown>
type EchoCallback<TPayload extends EchoPayload> = (payload: TPayload) => void

interface PrivateChannel<TPayload extends EchoPayload> {
  listen(event: string, callback: EchoCallback<TPayload>): PrivateChannel<TPayload>
  stopListening(event: string, callback?: EchoCallback<TPayload>): PrivateChannel<TPayload>
}

export interface ApplicationStatusChangedPayload extends EchoPayload {
  application_id: number
  candidate_id: number
  employer_id: number
  job_listing_id: number | null
  status: 'submitted' | 'under_review' | 'accepted' | 'rejected' | 'cancelled'
  changed_at: string
}

export function useEcho() {
  const cleanups: Array<() => void> = []

  function subscribePrivate<TPayload extends EchoPayload>(
    channelName: string,
    eventName: string,
    callback: EchoCallback<TPayload>,
  ): () => void {
    const echo = getEcho()
    const channel = echo.private(channelName) as PrivateChannel<TPayload>

    channel.listen(eventName, callback)

    const cleanup = () => {
      channel.stopListening(eventName, callback)
      echo.leave(channelName)
    }

    cleanups.push(cleanup)

    return cleanup
  }

  function closeAll(): void {
    while (cleanups.length > 0) {
      cleanups.pop()?.()
    }
  }

  onBeforeUnmount(closeAll)

  return {
    closeAll,
    prepareRealtimeAuth,
    subscribePrivate,
  }
}
