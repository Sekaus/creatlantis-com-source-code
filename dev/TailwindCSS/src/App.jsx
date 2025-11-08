import { useState } from 'react'
import './App.css'
import noteIcon from'../../../public/images/icons/noteIcon.webp'
import logo from '../../../public/images/TestIcon.webp'

function App() {
  const [count, setCount] = useState(0)

  return (
    <>
    <nav id='navigation-bar'>
      <a href='#' alt='Home'><img src={logo} alt='Webside logo icon'/></a>
      <a href='#' alt='Notes'><img src={noteIcon} alt='Note icon'/></a>
    </nav>
    
    <div id='profile-container'>
      <div id="custom-profile">
        <div className='profile-element'>test</div>
        <div className='profile-element'>test</div>
        <div className='profile-element'>test</div>
      </div>
    </div>
    </>
    )
}

export default App
