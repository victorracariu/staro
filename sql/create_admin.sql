SET storage_engine=INNODB;

CREATE TABLE User(
	Id INTEGER NOT NULL AUTO_INCREMENT,
	GroupId INTEGER,
	Username VARCHAR(32),
	Password VARCHAR(32),
	Passtest VARCHAR(32),
	Email VARCHAR(128),
	PersonName VARCHAR(64),
	PersonCode VARCHAR(32),
	PersonTitle VARCHAR(32),
	LocationId INTEGER,
	AgentId INTEGER,
	CenterId INTEGER,
	LoggedIn SMALLINT,
	LastActivity INTEGER,
	CreateTime TIMESTAMP,
	UpdateTime TIMESTAMP,
	CreateUserId INTEGER,
	UpdateUserId INTEGER,
	Notes BLOB,
	PRIMARY KEY (Id),
	UNIQUE KEY Username(Username)) ENGINE=INNODB;

CREATE TABLE UserGroup(
	Id INTEGER NOT NULL AUTO_INCREMENT,
	Name VARCHAR(32),
	Description VARCHAR(64),
	CreateTime TIMESTAMP,
	UpdateTime TIMESTAMP,
	CreateUserId INTEGER,
	UpdateUserId INTEGER,
	Notes BLOB,
	PRIMARY KEY (Id),
	UNIQUE KEY Name(Name) ) ENGINE=INNODB;
		
CREATE TABLE UserRight(
	Id INTEGER NOT NULL AUTO_INCREMENT,
	ParentId INTEGER,
	Module VARCHAR(64),
	Model VARCHAR(64),
	CanAccess SMALLINT,
	CanInsert SMALLINT,
	CanUpdate SMALLINT,
	CanDelete SMALLINT,
	CanUnpost SMALLINT,
	CreateTime TIMESTAMP,
	UpdateTime TIMESTAMP,
	CreateUserId INTEGER,
	UpdateUserId INTEGER,
	Notes BLOB,
	PRIMARY KEY (Id),
	KEY ParentId(ParentId) ) ENGINE=INNODB;
	
CREATE TABLE UserConfig(
	Id INTEGER NOT NULL AUTO_INCREMENT,
	GroupId INTEGER,
	Code VARCHAR(32),
	Name VARCHAR(64),
	Type VARCHAR(32),
	Value VARCHAR(64),
	CreateTime TIMESTAMP,
	UpdateTime TIMESTAMP,
	CreateUserId INTEGER,
	UpdateUserId INTEGER,
	Notes BLOB,
	PRIMARY KEY (Id) ) ENGINE=INNODB;
	
CREATE TABLE UserAudit(
	Id INTEGER NOT NULL AUTO_INCREMENT,
	UserId INTEGER,
	ActionDate DATE,
	ActionTime TIME,
	ActionCode VARCHAR(3),
	Model VARCHAR(32),
	RecordId INTEGER,
	CreateTime TIMESTAMP,
	UpdateTime TIMESTAMP,
	CreateUserId INTEGER,
	UpdateUserId INTEGER,
	Notes BLOB,
	PRIMARY KEY (Id) ) ENGINE=INNODB;
	
CREATE TABLE Generator(
	Id INTEGER NOT NULL AUTO_INCREMENT,
	Name VARCHAR(64),
	NextKey INTEGER,
	CreateTime TIMESTAMP,
	UpdateTime TIMESTAMP,
	CreateUserId INTEGER,
	UpdateUserId INTEGER,
	Notes BLOB,
	PRIMARY KEY (Id) ) ENGINE=INNODB;	

CREATE TABLE GeneratorNr(
	Id INTEGER NOT NULL AUTO_INCREMENT,
	Model VARCHAR(32),
	LocationId INTEGER,
	GenPrefix VARCHAR(8),
	GenValue INTEGER,
	GenDigits INTEGER,
	CreateTime TIMESTAMP,
	UpdateTime TIMESTAMP,
	CreateUserId INTEGER,
	UpdateUserId INTEGER,
	Notes BLOB,
	PRIMARY KEY (Id) ) ENGINE=INNODB;
	
CREATE TABLE Country(
	Id INTEGER NOT NULL AUTO_INCREMENT,
	Code VARCHAR(8),
	Name VARCHAR(64),
	Zone VARCHAR(64),
	Capital VARCHAR(32),
	Currency VARCHAR(100),
	CreateTime TIMESTAMP,
	UpdateTime TIMESTAMP,
	CreateUserId INTEGER,
	UpdateUserId INTEGER,
	Notes BLOB,
	PRIMARY KEY (Id),
	UNIQUE KEY Code(Code) ) ENGINE=INNODB;
	
CREATE TABLE Region(
	Id INTEGER NOT NULL AUTO_INCREMENT,
	Code VARCHAR(8),
	Name VARCHAR(32),
	Country VARCHAR(8),
	CreateTime TIMESTAMP,
	UpdateTime TIMESTAMP,
	CreateUserId INTEGER,
	UpdateUserId INTEGER,
	Notes BLOB,
	PRIMARY KEY (Id),
	UNIQUE KEY Code(Code) ) ENGINE=INNODB;

CREATE TABLE City(
	Id INTEGER NOT NULL AUTO_INCREMENT,
	Name VARCHAR(32),
	Country VARCHAR(8),
	Region VARCHAR(8),
	CreateTime TIMESTAMP,
	UpdateTime TIMESTAMP,
	CreateUserId INTEGER,
	UpdateUserId INTEGER,
	Notes BLOB,
	PRIMARY KEY (Id),
	UNIQUE KEY Name(Name) ) ENGINE=INNODB;
	
CREATE TABLE CurrencyType(
	Id INTEGER NOT NULL AUTO_INCREMENT,
	Code VARCHAR(3),
	Name VARCHAR(32),
	CreateTime TIMESTAMP,
	UpdateTime TIMESTAMP,
	CreateUserId INTEGER,
	UpdateUserId INTEGER,
	Notes BLOB,
	PRIMARY KEY (Id),
	UNIQUE KEY Code(Code) ) ENGINE=INNODB;
	
CREATE TABLE CurrencyRate(
	Id INTEGER NOT NULL AUTO_INCREMENT,
	Currency VARCHAR(3),
	ExchDate DATE,
	ExchRate NUMERIC(20,6),
	CreateTime TIMESTAMP,
	UpdateTime TIMESTAMP,
	CreateUserId INTEGER,
	UpdateUserId INTEGER,
	Notes BLOB,
	PRIMARY KEY (Id) ) ENGINE=INNODB;	
	
CREATE TABLE Company(
	Id INTEGER NOT NULL AUTO_INCREMENT,
	Code VARCHAR(32),
	Name VARCHAR(64),
	FiscalCode VARCHAR(32),
	RegisterCode VARCHAR(32),
	RegisterDate DATE,
	IndustryCode VARCHAR(32),
	BankName VARCHAR(64),
	BankAccount VARCHAR(64),
	AddressStreet VARCHAR(256),
	AddressCity VARCHAR(32),
	AddressRegion VARCHAR(32),
	AddressZipCode VARCHAR(32),
	AddressCountry VARCHAR(32),
	Phone VARCHAR(64),
	Fax VARCHAR(64),
	Email VARCHAR(64),
	Website VARCHAR(64),
	LogoUrl VARCHAR(255),
	CompanyCapital VARCHAR(64),
	CompanyTypeId INTEGER,
	RegisterTypeId INTEGER,
	LevelTypeId INTEGER,
	OwnerTypeId INTEGER,
	IndustryTypeId INTEGER,
	RepPerson VARCHAR(256),
	RepFunction VARCHAR(256),
	CreateTime TIMESTAMP,
	UpdateTime TIMESTAMP,
	CreateUserId INTEGER,
	UpdateUserId INTEGER,
	Version INTEGER,
	Notes BLOB,
	PRIMARY KEY (Id) ) ENGINE=INNODB;	
	
CREATE TABLE Config(
	Id INTEGER NOT NULL AUTO_INCREMENT,
	Code VARCHAR(32),
	Name VARCHAR(64),
	Type VARCHAR(32),
	Value VARCHAR(64),
	CreateTime TIMESTAMP,
	UpdateTime TIMESTAMP,
	CreateUserId INTEGER,
	UpdateUserId INTEGER,
	Notes BLOB,
	PRIMARY KEY (Id),
	UNIQUE KEY Code(Code) ) ENGINE=INNODB;
	
CREATE TABLE Location(
	Id INTEGER NOT NULL AUTO_INCREMENT,
	Code VARCHAR(32),
	Name VARCHAR(64),
	AddressStreet VARCHAR(256),
	AddressCity VARCHAR(32),
	AddressRegion VARCHAR(32),
	AddressZipCode VARCHAR(32),
	AddressCountry VARCHAR(32),
	Phone VARCHAR(64),
	Fax VARCHAR(64),
	Email VARCHAR(64),
	CreateTime TIMESTAMP,
	UpdateTime TIMESTAMP,
	CreateUserId INTEGER,
	UpdateUserId INTEGER,
	Notes BLOB,
	PRIMARY KEY (Id),
	UNIQUE KEY Code(Code) ) ENGINE=INNODB;
	
CREATE TABLE Center(
	Id INTEGER NOT NULL AUTO_INCREMENT,
	Code VARCHAR(32),
	Name VARCHAR(64),
	CreateTime TIMESTAMP,
	UpdateTime TIMESTAMP,
	CreateUserId INTEGER,
	UpdateUserId INTEGER,
	Notes BLOB,
	PRIMARY KEY (Id),
	UNIQUE KEY Code(Code) ) ENGINE=INNODB;

CREATE TABLE Agent(
	Id INTEGER NOT NULL AUTO_INCREMENT,
	Code VARCHAR(32),
	Name VARCHAR(64),
	CreateTime TIMESTAMP,
	UpdateTime TIMESTAMP,
	CreateUserId INTEGER,
	UpdateUserId INTEGER,
	Notes BLOB,
	PRIMARY KEY (Id),
	UNIQUE KEY Code(Code) ) ENGINE=INNODB;

CREATE TABLE Period(
	Id INTEGER NOT NULL AUTO_INCREMENT,
	Name VARCHAR(128),
	DateFrom DATE,
	DateTo DATE,
	Closed SMALLINT,
	CreateTime TIMESTAMP,
	UpdateTime TIMESTAMP,
	CreateUserId INTEGER,
	UpdateUserId INTEGER,
	Notes BLOB,
	PRIMARY KEY (Id) ) ENGINE=INNODB;

CREATE TABLE PaymentType(
	Id INTEGER NOT NULL AUTO_INCREMENT,
	Code VARCHAR(32),
	Name VARCHAR(64),
	DueDays INTEGER,
	CreateTime TIMESTAMP,
	UpdateTime TIMESTAMP,
	CreateUserId INTEGER,
	UpdateUserId INTEGER,
	Notes BLOB,
	PRIMARY KEY (Id),
	UNIQUE KEY Code(Code) ) ENGINE=INNODB;

CREATE TABLE DeliveryType(
	Id INTEGER NOT NULL AUTO_INCREMENT,
	Code VARCHAR(32),
	Name VARCHAR(64),
	DueDays INTEGER,
	CreateTime TIMESTAMP,
	UpdateTime TIMESTAMP,
	CreateUserId INTEGER,
	UpdateUserId INTEGER,
	Notes BLOB,
	PRIMARY KEY (Id),
	UNIQUE KEY Code(Code) ) ENGINE=INNODB;

CREATE TABLE DocumentType(
	Id INTEGER NOT NULL AUTO_INCREMENT,
	Code VARCHAR(32),
	Name VARCHAR(64),
	Model VARCHAR(64),
	StockKeep SMALLINT,
	IsPositive SMALLINT,
	IsNegative SMALLINT,
	CreateTime TIMESTAMP,
	UpdateTime TIMESTAMP,
	CreateUserId INTEGER,
	UpdateUserId INTEGER,
	Notes BLOB,
	PRIMARY KEY (Id),
	UNIQUE KEY Code(Code) ) ENGINE=INNODB;
	
CREATE TABLE DocumentStatus(
	Id INTEGER NOT NULL AUTO_INCREMENT,
	Code VARCHAR(32),
	Name VARCHAR(64),
	ParentCodes VARCHAR(250),
	CreateTime TIMESTAMP,
	UpdateTime TIMESTAMP,
	CreateUserId INTEGER,
	UpdateUserId INTEGER,
	Notes BLOB,
	PRIMARY KEY (Id),
	UNIQUE KEY Code(Code) ) ENGINE=INNODB;

CREATE TABLE DocumentTax(
	Id INTEGER NOT NULL AUTO_INCREMENT,
	Code VARCHAR(32),
	Name VARCHAR(250),
	Percent REAL,
	IsPositive SMALLINT,
	IsNegative SMALLINT,
	IsDefault SMALLINT,
	DocumentCode VARCHAR(32),
	CreateTime TIMESTAMP,
	UpdateTime TIMESTAMP,
	CreateUserId INTEGER,
	UpdateUserId INTEGER,
	Notes BLOB,
	PRIMARY KEY (Id),
	UNIQUE KEY Code(Code) ) ENGINE=INNODB;
	
CREATE TABLE CompanyType(
	Id INTEGER NOT NULL AUTO_INCREMENT,
	Code VARCHAR(32),
	Name VARCHAR(200),
	CreateTime TIMESTAMP,
	UpdateTime TIMESTAMP,
	CreateUserId INTEGER,
	UpdateUserId INTEGER,
	Notes BLOB,
	PRIMARY KEY (Id) ) ENGINE=INNODB;

CREATE TABLE CompanyRegisterType(
	Id INTEGER NOT NULL AUTO_INCREMENT,
	Code VARCHAR(32),
	Name VARCHAR(200),
	CreateTime TIMESTAMP,
	UpdateTime TIMESTAMP,
	CreateUserId INTEGER,
	UpdateUserId INTEGER,
	Notes BLOB,
	PRIMARY KEY (Id) ) ENGINE=INNODB;
		
CREATE TABLE CompanyLevelType(
	Id INTEGER NOT NULL AUTO_INCREMENT,
	Code VARCHAR(32),
	Name VARCHAR(200),
	CreateTime TIMESTAMP,
	UpdateTime TIMESTAMP,
	CreateUserId INTEGER,
	UpdateUserId INTEGER,
	Notes BLOB,
	PRIMARY KEY (Id) ) ENGINE=INNODB;
	
CREATE TABLE CompanyOwnerType(
	Id INTEGER NOT NULL AUTO_INCREMENT,
	Code VARCHAR(32),
	Name VARCHAR(200),
	CreateTime TIMESTAMP,
	UpdateTime TIMESTAMP,
	CreateUserId INTEGER,
	UpdateUserId INTEGER,
	Notes BLOB,
	PRIMARY KEY (Id) ) ENGINE=INNODB;
	
CREATE TABLE CompanyIndustryType(
	Id INTEGER NOT NULL AUTO_INCREMENT,
	Code VARCHAR(32),
	Name VARCHAR(200),
	CreateTime TIMESTAMP,
	UpdateTime TIMESTAMP,
	CreateUserId INTEGER,
	UpdateUserId INTEGER,
	Notes BLOB,
	PRIMARY KEY (Id) ) ENGINE=INNODB;
	