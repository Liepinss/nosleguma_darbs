const AUTH_TOKEN_KEY = 'spa_auth_token'

export function getAuthToken() {
  return localStorage.getItem(AUTH_TOKEN_KEY)
}

export function setAuthToken(token) {
  if (token) localStorage.setItem(AUTH_TOKEN_KEY, token)
  else localStorage.removeItem(AUTH_TOKEN_KEY)
}

/**
 * @param {string} path - e.g. "/login" (base /api is prepended)
 * @param {RequestInit & { admin?: boolean, token?: string | null, json?: object }} [options]
 */
export async function apiFetch(path, options = {}) {
  const url = path.startsWith('http') ? path : `/api${path.startsWith('/') ? '' : '/'}${path}`
  const headers = new Headers(options.headers || {})
  if (!headers.has('Accept')) {
    headers.set('Accept', 'application/json')
  }

  /** Admin API izmanto to pašu Sanctum žetonu kā parastajam kontam (is_admin serverī). */
  const token = options.token !== undefined ? options.token : getAuthToken()

  if (token) {
    headers.set('Authorization', `Bearer ${token}`)
  }

  let body = options.body
  if (options.json !== undefined) {
    headers.set('Content-Type', 'application/json')
    body = JSON.stringify(options.json)
  }

  const res = await fetch(url, { ...options, headers, body })

  const text = await res.text()
  let data = null
  if (text) {
    try {
      data = JSON.parse(text)
    } catch {
      data = { message: text }
    }
  }

  if (!res.ok) {
    const err = new Error(data?.message || res.statusText || 'Request failed')
    err.status = res.status
    err.body = data
    throw err
  }

  return data
}

export function firstValidationMessage(err) {
  const errors = err?.body?.errors
  if (errors && typeof errors === 'object') {
    const firstKey = Object.keys(errors)[0]
    const arr = errors[firstKey]
    if (Array.isArray(arr) && arr[0]) return arr[0]
  }
  return err?.message || 'Kļūda'
}
