# lumen-ddd-sample
Sample application on domain driven approach while using lumen + doctrine2.


## Why doctrine 2?
Because we need ORM that is implementing data mapper pattern. Active record is not a good tool to use when trying to implement domain aproach.

Reason we need data mapper is to separate domain from database layer. With active record ORM it can't be done.


## Architecture
Application have 3 layers:

- Domain
- Infrastructure
- Application

Each layer is split into modules. Each of modules does represent domain bounded context.
There is one special module: Common.

#### Domain layer
Domain layer should not use any external tools. Domain should not be tied to a framework or any external library.
However, in real world it is not always possible. Good example is doctrine2 that is forcing us to use its definitions of Collections.

#### Models
In ddd approach each bounded context should use only own domains. But it does force a developer to copy a lot of code.
If we have 2 bounded contexts like in out sample:  Accounts and Sales.
Both of them use definition of User. So they both should have own implementations of User.
But this does lead to copy-paste type coding. To avoid that we put UserContract to Common.
And since only Accounts does implement any business logic regarding User model, implementation is in Accounts.
And Sales does use UserContract, but does not have own implementation of User model.

From other hand we have AdminPanel module. AdminPanel does have own logic when it comes to User.
Therefore, it does define own User model (and related models).

Thanks to this approach we create domain own models only there where they are needed. 
Domain that does not add any logic because it only does consume definition, does not define own models.
But one that does add new logic in given context, should have own models. It will allow us few things:

- limit amount of code per model (new domains do not add code to existing models)
- define and understand what given model does and is used for in given context
- if needed, move whole module from our application to a new application. We have an option to split monolith to microservices

#### Common module
There is a Common domain where we can add models that are common for all domain to avoid replicating them.
Common domain can be also used as a place to keep own wrappers around external libraries.

If there is no need or time to write own solutions for every small problem. It is a good idea to use external library.
But we should not use it in domain layer (it should stay framework/external tools agnostic). 

Using external libraries in our domains will be problematic if in future we try to replace it with another one.
So better approach is to use Common to create own class that will use external library to implement what we need. 
And if we will have to swap that library into another one that wrapper class is only one we will have to change.

#### Cross domain communication
Communication between domains is handled by events. There is a CrossDomainSubscriber that allows to pass events from one domain to other one.

Domains should not be tied to each other. Domain can use only own definitions + common domain definitions.

#### Infrastructure layer
This is a layers that does implement some domains contracts that do require communication with external applications/services.

Domain does define abstraction (contract) and infrastructure does provide implementation.

If we need tools to communicate with external services like RabbitMq or some REST API - it is a good place to do it here.

#### Application layer
Api, workers, command line tools etc.
This layers does combine 2 other layers bellow into application.
