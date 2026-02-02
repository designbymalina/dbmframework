### Introduction

DBM Framework v5 is a **modular monolith** PHP framework designed for building long-living, maintainable applications with full control over architecture and performance.

Earlier versions of DBM Framework were implemented as a classic monolith. 
Version 5 introduces a **modular architecture**, allowing applications to be structured as independent, well-isolated modules while still being deployed as a single system.

This approach combines the advantages of monolithic applications (simplicity, performance, deployment) with the benefits of modular design (separation of concerns, scalability, maintainability).

## Architectural philosophy

Unlike frameworks such as Symfony or Laravel, DBM Framework:

- does not enforce heavy abstractions or over-engineered layers  
- avoids unnecessary magic and hidden behavior  
- favors explicit configuration and predictable execution flow  
- keeps the application structure close to the underlying HTTP and PHP runtime  

DBM Framework is designed for developers who want to **understand and control the entire application lifecycle**, from request handling to rendering the response.

## Modular monolith

In DBM Framework v5:

- the application is structured into logical modules  
- modules share a single runtime and deployment  
- boundaries are defined by responsibility, not by infrastructure  
- no microservices or distributed complexity is required  

This makes DBM Framework especially suitable for projects that must grow over time
without accumulating architectural debt.

## DBM Platform and CMS ecosystem

DBM Framework is also the foundation of the **DBM Platform**, including **DBM CMS**.

CMS Lite is a fast, lightweight, and secure solution for building websites where
files, templates, and routing give full control over the system.

For projects that require content management without direct file editing,
CMS Lite can be extended with CMS Lite + Admin, which adds:

- browser-based administration panel  
- secure authentication  
- content editing without touching the code  
- preservation of lightweight architecture  

The CMS is delivered as an extension module, allowing it to be installed
on existing projects without rebuilding the application.

## Who DBM Framework is for

DBM Framework is designed for:

- web agencies  
- freelancers  
- small and medium-sized businesses  
- internal systems and intranet applications  
- long-term projects requiring architectural stability  

## What this documentation covers

This documentation focuses on:

- application structure  
- controllers and services  
- routing and dependency injection  
- request lifecycle  
- modular architecture

Marketing details and sales aspects have been omitted.
