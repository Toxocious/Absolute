const openNavButton = document.getElementById('navButton');
const navOverlay = document.getElementsByTagName('nav')[0];
const navContainer = document.getElementsByClassName('nav-container')[0];

let navIsOpen = false;
openNavButton.addEventListener('click', (e) => {
  if ( navIsOpen )
    return;

  console.log('Opening nav');
  OpenNav();
  navIsOpen = true;
  e.stopImmediatePropagation();
});

navOverlay.addEventListener('click', (e) => {
  if ( !navIsOpen )
    return;

  console.log('Closing nav');
  CloseNav();
  navIsOpen = false;
});

function OpenNav()
{
  const navOverlayStyles = [
    { propName: 'width', propValue: '100%' },
    { propName: 'height', propValue: '100vh' },
    { propName: 'backgroundColor', propValue: 'rgba(0, 0, 0, 0.5)' }
  ]

  const navContainerStyles = [
    { propName: 'zIndex', propValue: '5' },
    { propName: 'width', propValue: '200px' },
    { propName: 'position', propValue: 'absolute' },
    { propName: 'height', propValue: '100vh' },
    { propName: 'display', propValue: 'flex' },
    { propName: 'borderRight', propValue: '2px solid var(--color-primary)', isCssVar: false },
    { propName: 'backgroundColor', propValue: '--color-sexary', isCssVar: true }
  ]

  for ( const navStyle of navOverlayStyles )
  {
    const styleName = navStyle.propName;

    if ( navStyle.isCssVar )
    {
      cssVarValue = getComputedStyle(document.documentElement).getPropertyValue(navStyle.propValue);
      navOverlay.style[styleName] = cssVarValue;
    }
    else
    {
      navOverlay.style[styleName] = navStyle.propValue;
    }
  }

  for ( const navStyle of navContainerStyles )
  {
    const styleName = navStyle.propName;

    if ( navStyle.isCssVar )
    {
      cssVarValue = getComputedStyle(document.documentElement).getPropertyValue(navStyle.propValue);
      navContainer.style[styleName] = cssVarValue;
    }
    else
    {
      navContainer.style[styleName] = navStyle.propValue;
    }
  }

  for ( const Child of Array.from(navContainer.children) )
  {
    Child.style.display = 'block';
    Child.style.width = '100%';
  }
}

function CloseNav()
{
  navOverlay.style.background = '';
  navOverlay.style.width = '0px';

  navContainer.style.display = 'none';
}
