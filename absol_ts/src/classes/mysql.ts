import * as mysql from 'mysql2/promise';

export default class MySQL {
  private static _instance: MySQL;
  private connection: mysql.Connection | undefined;

  public static get instance(): MySQL {
    return this._instance || (this._instance = new this());
  }

  public isConnected(): boolean {
    return this.connection !== null;
  }

  public static async doQuery(
    queryString: string,
    queryParams: any[]
  ): Promise<any[] | undefined> {
    try {
      const [rows, fields]: any = await this._instance.connection?.execute(
        queryString,
        queryParams
      );

      return rows as any[];
    } catch (error) {
      console.log(
        '[MySQL Query] An error occurred while performing a SQL quer. [Query]:',
        queryString,
        '[Params]:',
        queryParams,
        '[Error]:',
        error
      );
    }
  }

  public async connectDatabase(): Promise<void> {
    return mysql
      .createConnection({
        host: 'localhost',
        user: process.env.DATABASE_USER ?? 'absolute',
        password: process.env.DATABASE_PASSWORD ?? 'qwerty',
        database: process.env.DATABASE_TABLE ?? 'absolute',
      })
      .then((connection): void => {
        this.connection = connection;
      })
      .catch((error): void => {
        console.log(
          '[Chat | Connection] Failed to create MySQL connection for Absolute Chat. [Error]:',
          error
        );
      });
  }
}
