const Sequelize = require('sequelize');
const DATABASE_CONFIG = require('../config/database.json');
const FETCH_USER_MODEL = require('../models/FetchUser.js');

/**
 * Set up Sequelize so that we can query our database.
 */
const DATABASE = new Sequelize(DATABASE_CONFIG.DATABASE, DATABASE_CONFIG.USERNAME, DATABASE_CONFIG.PASSWORD, {
  host: DATABASE_CONFIG.HOST,
  dialect: DATABASE_CONFIG.DIALECT,
  logging: false,
});

exports.FetchUser = (User_ID) =>
{
  const USER = FETCH_USER_MODEL(DATABASE, Sequelize);
  
  return new Promise((resolve, reject) =>
  {
    let User_Data = [];
    
    if ( User_ID )
    {
      USER.findAll({
        attributes:
        [
          'ID',
          'Username',
          'Avatar',
          'Rank',
          'Power',
          'Chat_Ban',
          'Chat_Ban_Data',
          'Auth_Code'
        ],
        where:
        {
          ID: User_ID
        }
      })
      .then(user =>
      {
        if ( user )
        {
          user.map((user_item, index) =>
          {
            User_Data.push(user_item.dataValues);
          });
          
          resolve(User_Data);
        }
      })
      .catch(error =>
      {
        User_Data.push(error);

        resolve(User_Data);    
      });
    }
    else
    {
      resolve(User_Data);
    }
  });
}