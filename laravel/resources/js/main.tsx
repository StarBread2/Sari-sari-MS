import React from 'react'
import ReactDOM from 'react-dom/client'
import '../css/app.css' 

function App() {
    return <h1 className="text-3xl font-bold">React + TS + Laravel yawa 🚀</h1>
}

ReactDOM.createRoot(document.getElementById('app')!).render(
    <React.StrictMode>
        <App />
    </React.StrictMode>
)