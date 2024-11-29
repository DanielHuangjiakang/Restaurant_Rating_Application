--
-- Database Table Creation
--

-- Drop existing tables to avoid conflicts
DROP TABLE Include CASCADE CONSTRAINTS;
DROP TABLE Owner_Post_Promotion CASCADE CONSTRAINTS;
DROP TABLE Eat CASCADE CONSTRAINTS;
DROP TABLE About CASCADE CONSTRAINTS;
DROP TABLE Post CASCADE CONSTRAINTS;
DROP TABLE Uses_Promotion CASCADE CONSTRAINTS;
DROP TABLE User_Creates_FavoriteList CASCADE CONSTRAINTS;
DROP TABLE Favorite_List CASCADE CONSTRAINTS;
DROP TABLE Dishes CASCADE CONSTRAINTS;
DROP TABLE Cook CASCADE CONSTRAINTS;
DROP TABLE Chef CASCADE CONSTRAINTS;
DROP TABLE Waiter CASCADE CONSTRAINTS;
DROP TABLE Emp1 CASCADE CONSTRAINTS;
DROP TABLE Emp2 CASCADE CONSTRAINTS;
DROP TABLE Works_In CASCADE CONSTRAINTS;
DROP TABLE Promotion1 CASCADE CONSTRAINTS;
DROP TABLE Promotion2 CASCADE CONSTRAINTS;
DROP TABLE "User" CASCADE CONSTRAINTS;
DROP TABLE Restaurant CASCADE CONSTRAINTS;
DROP TABLE Owner CASCADE CONSTRAINTS;

--
-- Now, add each table.
--

-- Create Owner Table
CREATE TABLE Owner (
    ID INT PRIMARY KEY,
    name VARCHAR2(50)
);

-- Create Restaurant Table
CREATE TABLE Restaurant (
    name VARCHAR2(50) PRIMARY KEY,
    owner_ID INT NOT NULL,
    photo BLOB,
    rating DECIMAL(3,2) CHECK (rating BETWEEN 0 AND 5),
    FOREIGN KEY (owner_ID) REFERENCES Owner(ID)
);

-- Create Dishes Table
CREATE TABLE Dishes (
    restaurant_name VARCHAR2(50),
    name VARCHAR2(50),
    price DECIMAL(5,2) CHECK (price > 0),
    photo BLOB,
    PRIMARY KEY (restaurant_name, name),
    FOREIGN KEY (restaurant_name) REFERENCES Restaurant(name) ON DELETE CASCADE
);

-- Create Chef Table
CREATE TABLE Chef (
    ID INT PRIMARY KEY,
    style VARCHAR2(50),
    skill_level INT CHECK (skill_level > 0)
);

-- Create Cook Table
CREATE TABLE Cook (
    restaurant_name VARCHAR2(50),
    dish_name VARCHAR2(50),
    chef_id INT,
    PRIMARY KEY (restaurant_name, dish_name, chef_id),
    FOREIGN KEY (restaurant_name, dish_name) REFERENCES Dishes(restaurant_name, name) ON DELETE CASCADE,
    FOREIGN KEY (chef_id) REFERENCES Chef(ID)
);

-- Create User Table
CREATE TABLE "User" (
    ID INT PRIMARY KEY,
    name VARCHAR2(50) NOT NULL,
    age INT
);

-- Create Post Table
CREATE TABLE Post (
    ID INT PRIMARY KEY,
    user_id INT NOT NULL,
    photo BLOB,
    text VARCHAR2(999),
    FOREIGN KEY (user_id) REFERENCES "User"(ID)
);

-- Create Favorite_List Table
CREATE TABLE Favorite_List (
    ID INT PRIMARY KEY,
    date_create DATE,
    date_modify DATE
);

-- Create Promotion1 Table
CREATE TABLE Promotion1 (
    ID INT PRIMARY KEY,
    discount DECIMAL(5,2),
    date_start DATE,
    date_end DATE
);

-- Create Include Table
CREATE TABLE Include (
    favoriteListId INT,
    restaurantName VARCHAR2(50),
    PRIMARY KEY (favoriteListId, restaurantName),
    FOREIGN KEY (favoriteListId) REFERENCES Favorite_List(ID),
    FOREIGN KEY (restaurantName) REFERENCES Restaurant(name) ON DELETE CASCADE
);

-- Create Emp1 Table
CREATE TABLE Emp1 (
    ID INT PRIMARY KEY,
    name VARCHAR2(50),
    hours_per_week INT,
    hourly_wage DECIMAL(10,2)
);

-- Create Waiter Table
CREATE TABLE Waiter (
    ID INT PRIMARY KEY,
    language VARCHAR2(50),
    FOREIGN KEY (ID) REFERENCES Emp1(ID)
);

-- Create Emp2 Table
CREATE TABLE Emp2 (
    hours_per_week INT,
    hourly_wage DECIMAL(10,2),
    monthly_salary DECIMAL(10,2),
    PRIMARY KEY (hours_per_week, hourly_wage)
);

-- Create Works_In Table
CREATE TABLE Works_In (
    restaurant_name VARCHAR2(50),
    employee_ID INT,
    PRIMARY KEY (restaurant_name, employee_ID),
    FOREIGN KEY (restaurant_name) REFERENCES Restaurant(name) ON DELETE CASCADE,
    FOREIGN KEY (employee_ID) REFERENCES Emp1(ID)
);

-- Create Uses_Promotion Table
CREATE TABLE Uses_Promotion (
    promotion1_id INT,
    user_id INT,
    PRIMARY KEY (promotion1_id, user_id),
    FOREIGN KEY (user_id) REFERENCES "User"(ID),
    FOREIGN KEY (promotion1_id) REFERENCES Promotion1(ID)
);

-- Create User_Creates_FavoriteList Table
CREATE TABLE User_Creates_FavoriteList (
    user_id INT,
    favorite_list_id INT,
    PRIMARY KEY (user_id, favorite_list_id),
    FOREIGN KEY (user_id) REFERENCES "User"(ID),
    FOREIGN KEY (favorite_list_id) REFERENCES Favorite_List(ID)
);

-- Create Promotion2 Table
CREATE TABLE Promotion2 (
    date_start DATE,
    date_end DATE,
    duration INT,
    PRIMARY KEY (date_start, date_end)
);

-- Create About Table
CREATE TABLE About (
    post_id INT,
    restaurant_name VARCHAR2(50),
    PRIMARY KEY (post_id, restaurant_name),
    FOREIGN KEY (post_id) REFERENCES Post(ID),
    FOREIGN KEY (restaurant_name) REFERENCES Restaurant(name) ON DELETE CASCADE
);

-- Create Eat Table
CREATE TABLE Eat (
    user_id INT,
    restaurant_name VARCHAR2(50),
    dish_name VARCHAR2(50),
    PRIMARY KEY (user_id, restaurant_name, dish_name),
    FOREIGN KEY (user_id) REFERENCES "User"(ID),
    FOREIGN KEY (restaurant_name, dish_name) REFERENCES Dishes(restaurant_name, name) ON DELETE CASCADE
);

-- Create Owner_Post_Promotion Table
CREATE TABLE Owner_Post_Promotion (
    ownerID INT,
    promotionID INT,
    PRIMARY KEY (ownerID, promotionID),
    FOREIGN KEY (ownerID) REFERENCES Owner(ID),
    FOREIGN KEY (promotionID) REFERENCES Promotion1(ID)
);

--
-- Insert data into each table.
--

-- Insert data into Owner table
INSERT INTO Owner (ID, name) VALUES (301, 'Richard Roe');
INSERT INTO Owner (ID, name) VALUES (302, 'Anna Wilson');
INSERT INTO Owner (ID, name) VALUES (303, 'David King');
INSERT INTO Owner (ID, name) VALUES (304, 'Sophia Taylor');
INSERT INTO Owner (ID, name) VALUES (305, 'George Harris');
INSERT INTO Owner (ID, name) VALUES (306, 'Linda Scott');
INSERT INTO Owner (ID, name) VALUES (307, 'James Allen');
INSERT INTO Owner (ID, name) VALUES (308, 'Karen Hill');
INSERT INTO Owner (ID, name) VALUES (309, 'Steven Young');
INSERT INTO Owner (ID, name) VALUES (310, 'Patricia Clark');


-- Insert data into Restaurant table
INSERT INTO Restaurant (name, owner_ID, photo, rating) VALUES ('Chipotle', 301, NULL, 4.5);
INSERT INTO Restaurant (name, owner_ID, photo, rating) VALUES ('McDonalds', 302, NULL, 3.8);
INSERT INTO Restaurant (name, owner_ID, photo, rating) VALUES ('KFC', 303, NULL, 4.0);
INSERT INTO Restaurant (name, owner_ID, photo, rating) VALUES ('Burger King', 304, NULL, 3.9);
INSERT INTO Restaurant (name, owner_ID, photo, rating) VALUES ('Big Way', 305, NULL, 3.6);
INSERT INTO Restaurant (name, owner_ID, photo, rating) VALUES ('Tim Hortons', 306, NULL, 4.2);
INSERT INTO Restaurant (name, owner_ID, photo, rating) VALUES ('Subway', 307, NULL, 4.0);
INSERT INTO Restaurant (name, owner_ID, photo, rating) VALUES ('Blenz Coffee', 308, NULL, 4.3);
INSERT INTO Restaurant (name, owner_ID, photo, rating) VALUES ('DoughZone Dumpling House', 309, NULL, 4.5);
INSERT INTO Restaurant (name, owner_ID, photo, rating) VALUES ('Pizza Garden', 310, NULL, 4.1);

-- Insert data into Dishes table
INSERT INTO Dishes (restaurant_name, name, price, photo) VALUES ('Burger King', 'Burger', 6.99, NULL);
INSERT INTO Dishes (restaurant_name, name, price, photo) VALUES ('Big Way', 'Hot Pot', 12.50, NULL);
INSERT INTO Dishes (restaurant_name, name, price, photo) VALUES ('McDonalds', 'Burger', 5.99, NULL);
INSERT INTO Dishes (restaurant_name, name, price, photo) VALUES ('McDonalds', 'Fries', 3.50, NULL);
INSERT INTO Dishes (restaurant_name, name, price, photo) VALUES ('McDonalds', 'Chicken Nuggets', 4.99, NULL);
INSERT INTO Dishes (restaurant_name, name, price, photo) VALUES ('KFC', 'Chicken Wings', 9.99, NULL);
INSERT INTO Dishes (restaurant_name, name, price, photo) VALUES ('Tim Hortons', 'Bagel', 2.99, NULL);
INSERT INTO Dishes (restaurant_name, name, price, photo) VALUES ('Tim Hortons', 'Coffee', 1.99, NULL);
INSERT INTO Dishes (restaurant_name, name, price, photo) VALUES ('Subway', 'Turkey Sub', 6.99, NULL);
INSERT INTO Dishes (restaurant_name, name, price, photo) VALUES ('Subway', 'Veggie Sub', 5.99, NULL);
INSERT INTO Dishes (restaurant_name, name, price, photo) VALUES ('Blenz Coffee', 'Latte', 4.50, NULL);
INSERT INTO Dishes (restaurant_name, name, price, photo) VALUES ('Blenz Coffee', 'Matcha Latte', 4.99, NULL);
INSERT INTO Dishes (restaurant_name, name, price, photo) VALUES ('DoughZone Dumpling House', 'Pork Dumplings', 9.99, NULL);
INSERT INTO Dishes (restaurant_name, name, price, photo) VALUES ('DoughZone Dumpling House', 'Veggie Dumplings', 8.99, NULL);
INSERT INTO Dishes (restaurant_name, name, price, photo) VALUES ('Pizza Garden', 'Margherita Pizza', 10.99, NULL);
INSERT INTO Dishes (restaurant_name, name, price, photo) VALUES ('Pizza Garden', 'Pepperoni Pizza', 11.99, NULL);

-- Insert data into Chef table
INSERT INTO Chef (ID, style, skill_level) VALUES (101, 'Italian', 5);
INSERT INTO Chef (ID, style, skill_level) VALUES (102, 'Fast Food', 4);
INSERT INTO Chef (ID, style, skill_level) VALUES (103, 'American', 6);
INSERT INTO Chef (ID, style, skill_level) VALUES (104, 'Mexican', 5);
INSERT INTO Chef (ID, style, skill_level) VALUES (105, 'French', 7);
INSERT INTO Chef (ID, style, skill_level) VALUES (106, 'Canadian', 5);
INSERT INTO Chef (ID, style, skill_level) VALUES (107, 'Sandwiches', 4);
INSERT INTO Chef (ID, style, skill_level) VALUES (108, 'Coffee', 5);
INSERT INTO Chef (ID, style, skill_level) VALUES (109, 'Chinese', 6);
INSERT INTO Chef (ID, style, skill_level) VALUES (110, 'Italian', 7);

-- Insert data into Cook table
INSERT INTO Cook (restaurant_name, dish_name, chef_id) VALUES ('Burger King', 'Burger', 101);
INSERT INTO Cook (restaurant_name, dish_name, chef_id) VALUES ('Big Way', 'Hot Pot', 102);
INSERT INTO Cook (restaurant_name, dish_name, chef_id) VALUES ('McDonalds', 'Burger', 103);
INSERT INTO Cook (restaurant_name, dish_name, chef_id) VALUES ('McDonalds', 'Fries', 103);
INSERT INTO Cook (restaurant_name, dish_name, chef_id) VALUES ('KFC', 'Chicken Wings', 104);
INSERT INTO Cook (restaurant_name, dish_name, chef_id) VALUES ('Tim Hortons', 'Bagel', 106);
INSERT INTO Cook (restaurant_name, dish_name, chef_id) VALUES ('Tim Hortons', 'Coffee', 106);
INSERT INTO Cook (restaurant_name, dish_name, chef_id) VALUES ('Subway', 'Turkey Sub', 107);
INSERT INTO Cook (restaurant_name, dish_name, chef_id) VALUES ('Subway', 'Veggie Sub', 107);
INSERT INTO Cook (restaurant_name, dish_name, chef_id) VALUES ('Blenz Coffee', 'Latte', 108);
INSERT INTO Cook (restaurant_name, dish_name, chef_id) VALUES ('Blenz Coffee', 'Matcha Latte', 108);
INSERT INTO Cook (restaurant_name, dish_name, chef_id) VALUES ('DoughZone Dumpling House', 'Pork Dumplings', 109);
INSERT INTO Cook (restaurant_name, dish_name, chef_id) VALUES ('DoughZone Dumpling House', 'Veggie Dumplings', 109);
INSERT INTO Cook (restaurant_name, dish_name, chef_id) VALUES ('Pizza Garden', 'Margherita Pizza', 110);
INSERT INTO Cook (restaurant_name, dish_name, chef_id) VALUES ('Pizza Garden', 'Pepperoni Pizza', 110);

-- Insert data into User table
INSERT INTO "User" (ID, name, age) VALUES (1, 'Daniel', 21);
INSERT INTO "User" (ID, name, age) VALUES (2, 'Cosine', 20);
INSERT INTO "User" (ID, name, age) VALUES (3, 'SLC', 21);
INSERT INTO "User" (ID, name, age) VALUES (4, 'Alex', 22);
INSERT INTO "User" (ID, name, age) VALUES (5, 'Emma', 23);

-- Insert data into Post table
INSERT INTO Post (ID, user_id, photo, text) VALUES (1, 1, NULL, 'Great food and amazing service at Chipotle!');
INSERT INTO Post (ID, user_id, photo, text) VALUES (2, 2, NULL, 'McDonalds fries are unbeatable!');
INSERT INTO Post (ID, user_id, photo, text) VALUES (3, 3, NULL, 'KFC chicken wings are the best!');
INSERT INTO Post (ID, user_id, photo, text) VALUES (4, 4, NULL, 'Had a delicious burger at Burger King.');
INSERT INTO Post (ID, user_id, photo, text) VALUES (5, 5, NULL, 'Big Way offers the best hot pot in town.');

-- Insert data into Favorite_List table
INSERT INTO Favorite_List (ID, date_create, date_modify) VALUES (1, TO_DATE('2024-01-01', 'YYYY-MM-DD'), TO_DATE('2024-01-05', 'YYYY-MM-DD'));
INSERT INTO Favorite_List (ID, date_create, date_modify) VALUES (2, TO_DATE('2024-02-10', 'YYYY-MM-DD'), TO_DATE('2024-02-15', 'YYYY-MM-DD'));
INSERT INTO Favorite_List (ID, date_create, date_modify) VALUES (3, TO_DATE('2024-03-20', 'YYYY-MM-DD'), TO_DATE('2024-03-25', 'YYYY-MM-DD'));
INSERT INTO Favorite_List (ID, date_create, date_modify) VALUES (4, TO_DATE('2024-04-05', 'YYYY-MM-DD'), TO_DATE('2024-04-10', 'YYYY-MM-DD'));
INSERT INTO Favorite_List (ID, date_create, date_modify) VALUES (5, TO_DATE('2024-05-15', 'YYYY-MM-DD'), TO_DATE('2024-05-20', 'YYYY-MM-DD'));

-- Insert data into Promotion1 table
INSERT INTO Promotion1 (ID, discount, date_start, date_end) VALUES (1, 10.00, TO_DATE('2024-07-01', 'YYYY-MM-DD'), TO_DATE('2024-07-15', 'YYYY-MM-DD'));
INSERT INTO Promotion1 (ID, discount, date_start, date_end) VALUES (2, 15.00, TO_DATE('2024-08-01', 'YYYY-MM-DD'), TO_DATE('2024-08-20', 'YYYY-MM-DD'));
INSERT INTO Promotion1 (ID, discount, date_start, date_end) VALUES (3, 20.00, TO_DATE('2024-09-01', 'YYYY-MM-DD'), TO_DATE('2024-09-25', 'YYYY-MM-DD'));
INSERT INTO Promotion1 (ID, discount, date_start, date_end) VALUES (4, 5.00, TO_DATE('2024-10-01', 'YYYY-MM-DD'), TO_DATE('2024-10-10', 'YYYY-MM-DD'));
INSERT INTO Promotion1 (ID, discount, date_start, date_end) VALUES (5, 14.00, TO_DATE('2024-11-01', 'YYYY-MM-DD'), TO_DATE('2024-11-15', 'YYYY-MM-DD'));

-- Insert data into Include table
INSERT INTO Include (favoriteListId, restaurantName) VALUES (1, 'Chipotle');
INSERT INTO Include (favoriteListId, restaurantName) VALUES (2, 'McDonalds');
INSERT INTO Include (favoriteListId, restaurantName) VALUES (3, 'KFC');
INSERT INTO Include (favoriteListId, restaurantName) VALUES (4, 'Burger King');
INSERT INTO Include (favoriteListId, restaurantName) VALUES (5, 'Big Way');

-- Insert data into Emp1 table
INSERT INTO Emp1 (ID, name, hours_per_week, hourly_wage) VALUES (201, 'Mike Johnson', 40, 15.00);
INSERT INTO Emp1 (ID, name, hours_per_week, hourly_wage) VALUES (202, 'Sarah Lee', 35, 12.50);
INSERT INTO Emp1 (ID, name, hours_per_week, hourly_wage) VALUES (203, 'Alex Brown', 20, 18.00);
INSERT INTO Emp1 (ID, name, hours_per_week, hourly_wage) VALUES (204, 'Chris Green', 25, 14.00);
INSERT INTO Emp1 (ID, name, hours_per_week, hourly_wage) VALUES (205, 'Kim White', 30, 16.00);

-- Insert data into Waiter table
INSERT INTO Waiter (ID, language) VALUES (201, 'English');
INSERT INTO Waiter (ID, language) VALUES (202, 'Spanish');
INSERT INTO Waiter (ID, language) VALUES (203, 'French');
INSERT INTO Waiter (ID, language) VALUES (204, 'Chinese');
INSERT INTO Waiter (ID, language) VALUES (205, 'German');

-- Insert data into Emp2 table
INSERT INTO Emp2 (hours_per_week, hourly_wage, monthly_salary) VALUES (40, 15.00, 2400.00);
INSERT INTO Emp2 (hours_per_week, hourly_wage, monthly_salary) VALUES (35, 12.50, 1750.00);
INSERT INTO Emp2 (hours_per_week, hourly_wage, monthly_salary) VALUES (20, 18.00, 1440.00);
INSERT INTO Emp2 (hours_per_week, hourly_wage, monthly_salary) VALUES (25, 14.00, 1400.00);
INSERT INTO Emp2 (hours_per_week, hourly_wage, monthly_salary) VALUES (30, 16.00, 1920.00);

-- Insert data into Works_In table
INSERT INTO Works_In (restaurant_name, employee_ID) VALUES ('Chipotle', 201);
INSERT INTO Works_In (restaurant_name, employee_ID) VALUES ('McDonalds', 202);
INSERT INTO Works_In (restaurant_name, employee_ID) VALUES ('KFC', 203);
INSERT INTO Works_In (restaurant_name, employee_ID) VALUES ('Burger King', 204);
INSERT INTO Works_In (restaurant_name, employee_ID) VALUES ('Big Way', 205);

-- Insert data into Uses_Promotion table
INSERT INTO Uses_Promotion (promotion1_id, user_id) VALUES (1, 1);
INSERT INTO Uses_Promotion (promotion1_id, user_id) VALUES (2, 2);
INSERT INTO Uses_Promotion (promotion1_id, user_id) VALUES (3, 3);
INSERT INTO Uses_Promotion (promotion1_id, user_id) VALUES (4, 4);
INSERT INTO Uses_Promotion (promotion1_id, user_id) VALUES (5, 5);

-- Insert data into User_Creates_FavoriteList table
INSERT INTO User_Creates_FavoriteList (user_id, favorite_list_id) VALUES (1, 1);
INSERT INTO User_Creates_FavoriteList (user_id, favorite_list_id) VALUES (2, 2);
INSERT INTO User_Creates_FavoriteList (user_id, favorite_list_id) VALUES (3, 3);
INSERT INTO User_Creates_FavoriteList (user_id, favorite_list_id) VALUES (4, 4);
INSERT INTO User_Creates_FavoriteList (user_id, favorite_list_id) VALUES (5, 5);

-- Insert data into Promotion2 table
INSERT INTO Promotion2 (date_start, date_end, duration) VALUES (TO_DATE('2024-07-01', 'YYYY-MM-DD'), TO_DATE('2024-07-15', 'YYYY-MM-DD'), 14);
INSERT INTO Promotion2 (date_start, date_end, duration) VALUES (TO_DATE('2024-08-01', 'YYYY-MM-DD'), TO_DATE('2024-08-20', 'YYYY-MM-DD'), 19);
INSERT INTO Promotion2 (date_start, date_end, duration) VALUES (TO_DATE('2024-09-01', 'YYYY-MM-DD'), TO_DATE('2024-09-25', 'YYYY-MM-DD'), 24);
INSERT INTO Promotion2 (date_start, date_end, duration) VALUES (TO_DATE('2024-10-01', 'YYYY-MM-DD'), TO_DATE('2024-10-10', 'YYYY-MM-DD'), 9);
INSERT INTO Promotion2 (date_start, date_end, duration) VALUES (TO_DATE('2024-11-01', 'YYYY-MM-DD'), TO_DATE('2024-11-15', 'YYYY-MM-DD'), 14);
INSERT INTO Promotion2 (date_start, date_end, duration) VALUES (TO_DATE('2024-12-01', 'YYYY-MM-DD'), TO_DATE('2024-12-10', 'YYYY-MM-DD'), 9);

-- Insert data into About table
INSERT INTO About (post_id, restaurant_name) VALUES (1, 'Chipotle');
INSERT INTO About (post_id, restaurant_name) VALUES (2, 'McDonalds');
INSERT INTO About (post_id, restaurant_name) VALUES (3, 'KFC');
INSERT INTO About (post_id, restaurant_name) VALUES (4, 'Burger King');
INSERT INTO About (post_id, restaurant_name) VALUES (5, 'Big Way');

-- Insert data into Eat table
INSERT INTO Eat (user_id, restaurant_name, dish_name) VALUES (1, 'McDonalds', 'Burger');
INSERT INTO Eat (user_id, restaurant_name, dish_name) VALUES (1, 'McDonalds', 'Fries');
INSERT INTO Eat (user_id, restaurant_name, dish_name) VALUES (1, 'McDonalds', 'Chicken Nuggets');
INSERT INTO Eat (user_id, restaurant_name, dish_name) VALUES (1, 'Big Way', 'Hot Pot');
INSERT INTO Eat (user_id, restaurant_name, dish_name) VALUES (1, 'KFC', 'Chicken Wings');
INSERT INTO Eat (user_id, restaurant_name, dish_name) VALUES (1, 'Burger King', 'Burger');
INSERT INTO Eat (user_id, restaurant_name, dish_name) VALUES (1, 'Tim Hortons', 'Bagel');
INSERT INTO Eat (user_id, restaurant_name, dish_name) VALUES (1, 'Tim Hortons', 'Coffee');
INSERT INTO Eat (user_id, restaurant_name, dish_name) VALUES (1, 'Subway', 'Turkey Sub');
INSERT INTO Eat (user_id, restaurant_name, dish_name) VALUES (1, 'Subway', 'Veggie Sub');
INSERT INTO Eat (user_id, restaurant_name, dish_name) VALUES (1, 'Blenz Coffee', 'Latte');
INSERT INTO Eat (user_id, restaurant_name, dish_name) VALUES (1, 'Blenz Coffee', 'Matcha Latte');
INSERT INTO Eat (user_id, restaurant_name, dish_name) VALUES (1, 'DoughZone Dumpling House', 'Pork Dumplings');
INSERT INTO Eat (user_id, restaurant_name, dish_name) VALUES (1, 'DoughZone Dumpling House', 'Veggie Dumplings');
INSERT INTO Eat (user_id, restaurant_name, dish_name) VALUES (1, 'Pizza Garden', 'Margherita Pizza');
INSERT INTO Eat (user_id, restaurant_name, dish_name) VALUES (1, 'Pizza Garden', 'Pepperoni Pizza');
INSERT INTO Eat (user_id, restaurant_name, dish_name) VALUES (2, 'Big Way', 'Hot Pot');
INSERT INTO Eat (user_id, restaurant_name, dish_name) VALUES (3, 'McDonalds', 'Fries');
INSERT INTO Eat (user_id, restaurant_name, dish_name) VALUES (4, 'KFC', 'Chicken Wings');
INSERT INTO Eat (user_id, restaurant_name, dish_name) VALUES (5, 'Burger King', 'Burger');
INSERT INTO Eat (user_id, restaurant_name, dish_name) VALUES (2, 'Subway', 'Turkey Sub');
INSERT INTO Eat (user_id, restaurant_name, dish_name) VALUES (2, 'Subway', 'Veggie Sub');
INSERT INTO Eat (user_id, restaurant_name, dish_name) VALUES (3, 'Blenz Coffee', 'Latte');
INSERT INTO Eat (user_id, restaurant_name, dish_name) VALUES (3, 'Blenz Coffee', 'Matcha Latte');
INSERT INTO Eat (user_id, restaurant_name, dish_name) VALUES (4, 'DoughZone Dumpling House', 'Pork Dumplings');
INSERT INTO Eat (user_id, restaurant_name, dish_name) VALUES (4, 'DoughZone Dumpling House', 'Veggie Dumplings');
INSERT INTO Eat (user_id, restaurant_name, dish_name) VALUES (5, 'Pizza Garden', 'Margherita Pizza');
INSERT INTO Eat (user_id, restaurant_name, dish_name) VALUES (5, 'Pizza Garden', 'Pepperoni Pizza');

-- Insert data into Owner_Post_Promotion table
INSERT INTO Owner_Post_Promotion (ownerID, promotionID) VALUES (301, 1);
INSERT INTO Owner_Post_Promotion (ownerID, promotionID) VALUES (302, 2);
INSERT INTO Owner_Post_Promotion (ownerID, promotionID) VALUES (303, 3);
INSERT INTO Owner_Post_Promotion (ownerID, promotionID) VALUES (304, 4);
INSERT INTO Owner_Post_Promotion (ownerID, promotionID) VALUES (305, 5);
