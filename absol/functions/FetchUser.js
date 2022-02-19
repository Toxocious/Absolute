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
          'Is_Staff',
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
          user.map((user_item) =>
          {
            switch (user_item.dataValues.Rank)
            {
              case "Member":
                user_item.dataValues.Rank = 'member';
                break;
              case "Chat Moderator":
                user_item.dataValues.Rank = 'chat_mod';
                break;
              case "Moderator":
                user_item.dataValues.Rank = 'moderator';
                break;
              case "Super Moderator":
                user_item.dataValues.Rank = 'super_mod';
                break;
              case "Bot":
                user_item.dataValues.Rank = 'bot';
                break;
              case "Developer":
                user_item.dataValues.Rank = 'developer';
                break;
              case "Administrator":
                user_item.dataValues.Rank = 'administrator';
                break;
            }

            User_Data.push(user_item.dataValues);
          });

          resolve(User_Data);
        }
      })
      .catch(error =>
      {
        reject(error);
      });
    }
    else
    {
      reject(User_Data);
    }
  });
}
