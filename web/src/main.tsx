import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import './index.css'
import App from './App'

// Password-reset emails link back with ?reset=1; open the reset page once signed in.
if (new URLSearchParams(location.search).has('reset')) location.hash = '#/reset-password'

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <App />
  </StrictMode>,
)
