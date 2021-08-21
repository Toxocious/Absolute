const mysql = require('mysql');
const request = require('request');
const cheerio = require("cheerio");

var db = mysql.createConnection({
  host: "localhost",
  user: "absolute",
  password: "qwerty",
  database: "absolute"
});

db.connect(function(err) {
  if (err)
    throw err;

  db.query("SELECT `Name` FROM `moves` WHERE `temp_updated` = 0 ORDER BY `temp_updated` ASC", (error, rows, fields) => {
    if ( error )
      throw error;

    if ( rows.length < 1 )
      return 'No rows to process';

    for ( let i = 0; i < rows.length; i++ )
    {
      const URL = `https://bulbapedia.bulbagarden.net/wiki/${rows[i].Name}_(move)`;
      console.log(`Processing ${rows[i].Name} (${URL})`);

      request(URL, (err, res, body) =>
      {
        if ( typeof body !== 'string' )
          return;

        const $ = cheerio.load(body);

        const Affected_By = [];

        const Selectors = [
          '#mw-content-text > div > table:nth-child(1) > tbody > tr:nth-child(4) > td > table > tbody > tr:nth-child(7) > td > ul',
          '#mw-content-text > div > table:nth-child(2) > tbody > tr:nth-child(4) > td > table > tbody > tr:nth-child(7) > td > ul',
          '#mw-content-text > div > table:nth-child(3) > tbody > tr:nth-child(4) > td > table > tbody > tr:nth-child(7) > td > ul',
          '#mw-content-text > div > table:nth-child(4) > tbody > tr:nth-child(4) > td > table > tbody > tr:nth-child(7) > td > ul',
          '#mw-content-text > div > table:nth-child(5) > tbody > tr:nth-child(4) > td > table > tbody > tr:nth-child(7) > td > ul',
        ];

        let Found_Selector = false;
        for ( const Index in Selectors )
        {
          const Selector = Selectors[Index];

          if ( $(Selector).length > 0 )
          {
            Found_Selector = true;

            $(Selector).each((idx, el) =>
            {
              const ele_text = $(el).text();
              const Affect = { Name: null, Value: null };

              if ( ele_text.indexOf('Contact') > -1 )
              {
                Affect.Name = 'Contact';
                ele_text.indexOf('Makes Contact') > -1 ? Affect.Value = 1 : Affect.Value = 0;
                Affected_By.push({ Name: Affect.Name, Value: Affect.Value });
              }

              if ( ele_text.indexOf('Protect') > -1 )
              {
                Affect.Name = 'Protect';
                ele_text.indexOf('Not affected by Protect') > -1 ? Affect.Value = 0 : Affect.Value = 1;
                Affected_By.push({ Name: Affect.Name, Value: Affect.Value });
              }

              if ( ele_text.indexOf('Magic Coat') > -1 )
              {
                Affect.Name = 'Magic_Coat';
                ele_text.indexOf('Not affected by Magic Coat') > -1 ? Affect.Value = 0 : Affect.Value = 1;
                Affected_By.push({ Name: Affect.Name, Value: Affect.Value });
              }

              if ( ele_text.indexOf('Snatch') > -1 )
              {
                Affect.Name = 'Snatch';
                ele_text.indexOf('Not affected by Snatch') > -1 ? Affect.Value = 0 : Affect.Value = 1;
                Affected_By.push({ Name: Affect.Name, Value: Affect.Value });
              }

              if ( ele_text.indexOf('Mirror Move') > -1 )
              {
                Affect.Name = 'Mirror_Move';
                ele_text.indexOf('Not affected by Mirror Move') > -1 ? Affect.Value = 0 : Affect.Value = 1;
                Affected_By.push({ Name: Affect.Name, Value: Affect.Value });
              }

              if ( ele_text.indexOf('King\'s Rock') > -1 )
              {
                Affect.Name = 'Kings_Rock';
                ele_text.indexOf('Not affected by King\'s Rock') > -1 ? Affect.Value = 0 : Affect.Value = 1;
                Affected_By.push({ Name: Affect.Name, Value: Affect.Value });
              }
            });
          }
        }

        if ( Found_Selector )
        {
          for ( const SQL_Data in Affected_By )
          {
            const Data = Affected_By[SQL_Data];
            const SQL = `UPDATE \`moves\` SET \`${Data.Name}\` = ${Data.Value} WHERE \`Name\` = '${rows[i].Name}' LIMIT 1`;
            db.query(SQL, (error, result) => {
              if ( error )
                throw error;
            });
          }

          console.log(`${rows[i].Name} database fields have been updated`);

          db.query(`UPDATE \`moves\` SET \`temp_updated\` = 1 WHERE \`Name\` = '${rows[i].Name}' LIMIT 1`, (error, result) => {
            if ( error )
              throw error;
          });
        }
        else
          console.log(`[${rows[i].Name}] :: Failed to find the neccessary DOM element`);
      });
    }
  });
});
