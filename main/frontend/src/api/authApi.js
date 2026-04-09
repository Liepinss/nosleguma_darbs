import { apiFetch, setAuthToken } from './http'
import { mapUser } from './mappers'

const AUTH_USER_KEY = 'spa_auth_user'

export function getStoredUser() {
  try {
    const raw = localStorage.getItem(AUTH_USER_KEY)
    return raw ? JSON.parse(raw) : null
  } catch {
    return null
  }
}

export function setStoredUser(user) {
  if (user) localStorage.setItem(AUTH_USER_KEY, JSON.stringify(user))
  else localStorage.removeItem(AUTH_USER_KEY)
}

export function clearUserSession() {
  setAuthToken(null)
  setStoredUser(null)
}

export async function register(payload) {
  return apiFetch('/register', { method: 'POST', json: payload, token: null })
}

export async function login(payload) {
  const data = await apiFetch('/login', { method: 'POST', json: payload, token: null })
  setAuthToken(data.token)
  setStoredUser(mapUser(data.user))
  return data
}

export async function logoutUser() {
  try {
    await apiFetch('/logout', { method: 'POST' })
  } finally {
    clearUserSession()
  }
}

export async function fetchCurrentUser() {
  return apiFetch('/user', { method: 'GET' })
}

export async function refreshSessionUser() {
  const raw = await fetchCurrentUser()
  const u = mapUser(raw)
  setStoredUser(u)
  return u
}
