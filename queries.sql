-- Alexander Fisher
-- Dr. Ching-yu Huang
-- CPS5740
-- Database Website Project

-- THESE ARE ALL QUERIES USED TO BUILD THE PROJECT WEBSITE
use 2023F_fisheral;

-- SECTION 2 --
CREATE TABLE PRODUCT (
	id int primary key auto_increment not null,
    description varchar(100) not null,
    name varchar(50) not null,
    vendor_id int not null,
    cost DECIMAL(13,2) not null,
    sell_price DECIMAL(13,2) not null,
    quantity int not null,
    employee_id int not null
);

CREATE TABLE `ORDER` (
	id int primary key auto_increment not null,
    customer_id int not null,
    date datetime not null
);

CREATE TABLE PRODUCT_ORDER (
	order_id int not null,
    product_id int not null,
    quantity int not null
);

drop table PRODUCT_ORDER;
drop table PRODUCT;
drop table `ORDER`;

ALTER TABLE PRODUCT ADD FOREIGN KEY (vendor_id) references CPS5740.VENDOR(vendor_id);
ALTER TABLE PRODUCT ADD FOREIGN KEY (employee_id) references CPS5740.EMPLOYEE2(employee_id);
ALTER TABLE PRODUCT_ORDER ADD FOREIGN KEY (order_id) references `ORDER`(id);
ALTER TABLE PRODUCT_ORDER ADD FOREIGN KEY (product_id) references PRODUCT(id);
ALTER TABLE `ORDER` ADD FOREIGN KEY (customer_id) references 
