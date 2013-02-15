SQL Query Abstraction Layer
=============

The **php-sql-query** package aims to provide a consistent abstraction layer for SQL query construction. It's divided in two parts: 

1. 	abstract query components *(columns, tables, limit, groups)* 
2. renderers *(MySQL, PgSql, Sql Server)*

Creating a select is really straightforward : 

```php
$select = new \RBM\SqlQuery\Select();
$select->setTable("project");
$select->setColumns(["project_id", "name"]);
$select->limit(0, 10);
```
To output the correct query according to the database system in use, you'll have to instanciate the renderer, and call its render method :

```php	
$renderer = new \RBM\SqlQuery\RendererAdapter\MySql();
echo $renderer->render($select);
```
This will print the following string :

```php	
SELECT
	`project`.`project_id`
	, `project`.`name`
FROM
	`project`
LIMIT 0, 10
```	
Hopefully, you don't have to be *that* verbose to get the job done. 

```php
// do this once
\RBM\SqlQuery\Select::setDefaultRenderer("\RBM\SqlQuery\RendererAdapter");
// […]
$select = new \RBM\SqlQuery\Select("project", ["project_id", "name"]);
echo $select;
```
