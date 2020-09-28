module.exports = (sequelize, type) =>
{
  return sequelize.define('users',
  {
    ID: type.INTEGER,
    Username: type.STRING,
    Avatar: type.STRING,
    Power: type.INTEGER,
    Rank: type.STRING,
  });
}