/**
 * Create a new news post.
 */
function CreateNewsPost()
{
  let Form_Data = new FormData();
  Form_Data.append('Action', 'Create');

  if ( document.getElementsByName('News_Post_Title')[0].value == '' || document.getElementsByName('News_Post_Content')[0].value == '' )
  {
    alert('Please enter a news post title and content');
    return;
  }

  Form_Data.append('News_Title', document.getElementsByName('News_Post_Title')[0].value);
  Form_Data.append('News_Content', document.getElementsByName('News_Post_Content')[0].value);

  SendRequest('news_post', Form_Data)
    .then((Create_News_Post) => {
      const Create_News_Post_Data = JSON.parse(Create_News_Post);

      document.getElementById('News_AJAX').className = Create_News_Post_Data.Success ? 'success' : 'error';
      document.getElementById('News_AJAX').innerHTML = Create_News_Post_Data.Message;

      document.getElementsByName('News_Post_Title')[0].value = '';
      document.getElementsByName('News_Post_Content')[0].value = '';
    })
    .catch((Error) => console.error('[ECRPG] An error occurred while creating the news post:', Error));
}
