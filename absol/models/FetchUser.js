module.exports = (sequelize, type) =>
{
  return sequelize.define('users',
  {
    ID: type.INTEGER,
    Username: type.STRING,
    Avatar: type.STRING,
    Is_Staff: type.INTEGER,
    Rank: type.STRING,
  });
}
