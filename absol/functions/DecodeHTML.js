exports.DecodeHTML = (message) =>
{
  return message.replace(/&#([0-9]{1,3});/gi, function(match, num)
  {
    return String.fromCharCode(parseInt(num));
  });
};