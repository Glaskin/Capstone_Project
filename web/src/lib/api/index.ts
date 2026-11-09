import { createDemoApi } from './demo'
import { createSupabaseApi } from './supabase'

const url = import.meta.env.VITE_SUPABASE_URL as string | undefined
const key = import.meta.env.VITE_SUPABASE_ANON_KEY as string | undefined

/** Supabase when configured (see .env.example), otherwise the in-browser demo. */
export const api = url && key ? createSupabaseApi(url, key) : createDemoApi()
