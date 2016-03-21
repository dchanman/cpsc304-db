DROP TABLE SuggestedBy;
DROP TABLE InterestedIn;
DROP TABLE ActivityTime;
DROP TABLE Interest;
DROP TABLE Business;
DROP TABLE Message;
DROP TABLE Image;
DROP TABLE UnsuccessfulMatch;
DROP TABLE SuccessfulMatch;
DROP TABLE Users;

CREATE TABLE Users
(
UserID INTEGER NOT NULL,
Name CHAR(30) NOT NULL,
DateJoined Long NOT NULL,
Location CHAR(30) NOT NULL,
Age INTEGER NOT NULL,
Gender CHAR(1) NOT NULL,
Preference CHAR(2) NOT NULL,
PasswordHash CHAR(48),
PRIMARY KEY (UserID)
);

CREATE TABLE SuccessfulMatch
(
UserID1 INTEGER NOT NULL,
UserID2 INTEGER NOT NULL,
PRIMARY KEY (UserID1, UserID2),
FOREIGN KEY (UserID1) REFERENCES Users(UserID) ON DELETE CASCADE,
FOREIGN KEY (UserID2) REFERENCES Users(UserID) ON DELETE CASCADE,
Check (UserID1 < UserID2)
);

CREATE TABLE UnsuccessfulMatch
(
UserID1 INTEGER NOT NULL,
UserID2 INTEGER NOT NULL,
PRIMARY KEY (UserID1, UserID2),
FOREIGN KEY (UserID1) REFERENCES Users(UserID) ON DELETE CASCADE,
FOREIGN KEY (UserID2) REFERENCES Users(UserID) ON DELETE CASCADE,
Check (UserID1 < UserID2)
);

CREATE TABLE Image
(
UserID INTEGER NOT NULL,
DateAdded DATE NOT NULL,
ImageURL CHAR(2000) NOT NULL,
DisplayOrder INTEGER NOT NULL,
PRIMARY KEY (UserID),
FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
);

CREATE TABLE Message
(
MessageID INTEGER NOT NULL,
SenderUserID INTEGER NOT NULL,
ReceiverUserID INTEGER NOT NULL,
MessageChar CHAR(2000) NOT NULL,
SendScheduledTime TIMESTAMP NOT NULL,
FOREIGN KEY (SenderUserId) REFERENCES Users(UserID) ON DELETE CASCADE,
FOREIGN KEY (ReceiverUserID) REFERENCES Users(UserID) ON DELETE CASCADE,
PRIMARY KEY (MessageID)
);

CREATE TABLE Business
(
BusinessID CHAR(30) NOT NULL,
Location CHAR(50),
PasswordHash CHAR(48),
PRIMARY KEY (BusinessID)
);

CREATE TABLE Interest
(
InterestType CHAR(20) NOT NULL,
PRIMARY KEY (InterestType)
);

CREATE TABLE ActivityTime
(
Activity CHAR(50) NOT NULL,
BusinessName CHAR(30) NOT NULL,
ScheduledTime DATE NOT NULL,
DateLocation CHAR(50) NOT NULL,
Discount INTEGER,
PRIMARY KEY (Activity, BusinessName, ScheduledTime, DateLocation),
FOREIGN KEY (BusinessName) REFERENCES Business(BusinessID) ON DELETE CASCADE
);

CREATE TABLE InterestedIn
(
UserID INTEGER NOT NULL,
Interest CHAR(20) NOT NULL,
PRIMARY KEY (UserID, Interest),
FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE,
FOREIGN KEY (Interest) REFERENCES Interest(InterestType) ON DELETE CASCADE
);

CREATE TABLE SuggestedBy
(
ScheduledTime Date,
Location CHAR(50),
Discount CHAR(50),
ActivityName CHAR(50) NOT NULL,
BusinessName CHAR(30) NOT NULL,
PRIMARY KEY (ScheduledTime, Location, ActivityName, BusinessName),
FOREIGN KEY (ActivityName, BusinessName, ScheduledTime, Location) REFERENCES ActivityTime(Activity, BusinessName, ScheduledTime, DateLocation) ON DELETE CASCADE,
FOREIGN KEY (BusinessName) REFERENCES Business(BusinessID) ON DELETE CASCADE
);
