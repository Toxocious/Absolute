const openChatButton=document.getElementById('chatButton');const chatOverlay=document.getElementsByTagName('aside')[0];const chatContainer=document.getElementById('AbsoChat');const chatMessageInput=document.querySelector('#AbsoChat .foot');const openNavButton=document.getElementById('navButton');const navOverlay=document.getElementsByTagName('nav')[0];const navContainer=document.getElementsByClassName('nav-container')[0];let chatIsOpen=!1;let navIsOpen=!1;chatContainer.addEventListener('click',(e)=>{e.stopPropagation()});openChatButton.addEventListener('click',(e)=>{if(chatIsOpen)
return;OpenChat();chatIsOpen=!0;e.stopImmediatePropagation()});chatOverlay.addEventListener('click',(e)=>{if(!chatIsOpen)
return;CloseChat();chatIsOpen=!1});openNavButton.addEventListener('click',(e)=>{if(navIsOpen)
return;OpenNav();navIsOpen=!0;e.stopImmediatePropagation()});navOverlay.addEventListener('click',(e)=>{if(!navIsOpen)
return;CloseNav();navIsOpen=!1});function OpenNav(){const navOverlayStyles=[{propName:'width',propValue:'100%'},{propName:'height',propValue:'100vh'},{propName:'backgroundColor',propValue:'rgba(0, 0, 0, 0.5)'}];const navContainerStyles=[{propName:'zIndex',propValue:'5'},{propName:'width',propValue:'200px'},{propName:'position',propValue:'absolute'},{propName:'height',propValue:'100vh'},{propName:'display',propValue:'flex'},{propName:'borderRight',propValue:'2px solid var(--color-primary)'},{propName:'backgroundColor',propValue:'--color-sexary',isCssVar:!0}];for(const navStyle of navOverlayStyles){const styleName=navStyle.propName;if(navStyle.isCssVar){cssVarValue=getComputedStyle(document.documentElement).getPropertyValue(navStyle.propValue);navOverlay.style[styleName]=cssVarValue}else{navOverlay.style[styleName]=navStyle.propValue}}
for(const navStyle of navContainerStyles){const styleName=navStyle.propName;if(navStyle.isCssVar){cssVarValue=getComputedStyle(document.documentElement).getPropertyValue(navStyle.propValue);navContainer.style[styleName]=cssVarValue}else{navContainer.style[styleName]=navStyle.propValue}}
for(const Child of Array.from(navContainer.children)){Child.style.display='block';Child.style.width='100%'}}
function CloseNav(){navOverlay.style.background='';navOverlay.style.width='0px';navContainer.style.display='none'}
function OpenChat(){const chatOverlayStyles=[{propName:'width',propValue:'100%'},{propName:'height',propValue:'99vh'},{propName:'backgroundColor',propValue:'rgba(0, 0, 0, 0.5)'}];const chatContainerStyles=[{propName:'zIndex',propValue:'5'},{propName:'width',propValue:'184px'},{propName:'right',propValue:'0'},{propName:'position',propValue:'absolute'},{propName:'height',propValue:'99vh'},{propName:'flexDirection',propValue:'column'},{propName:'display',propValue:'flex'},];for(const chatStyle of chatOverlayStyles){const styleName=chatStyle.propName;if(chatStyle.isCssVar){cssVarValue=getComputedStyle(document.documentElement).getPropertyValue(chatStyle.propValue);chatOverlay.style[styleName]=cssVarValue}else{chatOverlay.style[styleName]=chatStyle.propValue}}
for(const chatStyle of chatContainerStyles){const styleName=chatStyle.propName;if(chatStyle.isCssVar){cssVarValue=getComputedStyle(document.documentElement).getPropertyValue(chatStyle.propValue);chatContainer.style[styleName]=cssVarValue}else{chatContainer.style[styleName]=chatStyle.propValue}}
chatOverlay.style.display='flex';chatMessageInput.style.position='absolute';chatMessageInput.style.bottom='0'}
function CloseChat(){chatOverlay.style.background='';chatOverlay.style.width='0px';chatContainer.style.display='none'}