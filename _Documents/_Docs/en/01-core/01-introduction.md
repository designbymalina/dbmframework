# Introduction

The **DBM Framework** is a modular monolith designed for building efficient and maintainable PHP applications. It provides full architectural control, enabling the creation of systems with a long lifecycle.

Unlike previous versions, which were based on a classic monolith, version 5 introduces a **modular architecture**. This allows for an application structure composed of independent, isolated modules that are still implemented as a cohesive system.

This solution combines the simplicity and efficiency of a **monolith** with the flexibility, scalability, and clear separation of responsibilities (Separation of Concerns) characteristic of **modular systems**.

## Modular Monolith in Practice

In the DBM Framework, architecture is not just a theory, but the foundation of the file structure:

- **Logical Partition**: The application is divided into isolated modules (the `modules/` directory) that share a single execution environment.

- **Boundaries of Responsibility**: Divisions are defined by business functionality, not infrastructure, eliminating unnecessary complexity in microservices.
- **Foundation and Plugins**: The engine (the `application/` directory) manages the logic, while modules such as CMS Lite or Admin are optional components installed via a dedicated Installer.
- **Full Freedom**: The framework allows you to build applications in the `src/` directory from scratch or use pre-built, standalone modules—without accumulating architectural debt.

## Key Idea

**DbM Framework is a lightweight application engine,
and CMS Lite is an optional content management module.**

This approach can be summarized as:

**Micro framework + optional CMS**

For the developer: full control and efficiency

For the customer: a simple content management panel

## Why choose DbM Framework?

- **No "magic"**: Explicit configuration and predictable data flow make debugging and development a breeze.
- **Zero overhead**: You only load the modules you need at the moment. The system remains lightweight regardless of scale.
- **Time-saving architecture**: Module isolation minimizes the risk of code entanglement (spaghetti code).
- **Deployment flexibility**: You can start with a simple showcase and expand it to a sophisticated SaaS system without changing the foundation.

## Architectural Philosophy

Unlike frameworks like Symfony or Laravel, DBM Framework:

- does not enforce extensive abstractions or overly complex layers
- avoids unnecessary magic and hidden behaviors
- favors explicit configuration and predictable execution
- keeps application structure close to the underlying HTTP and PHP runtime

DBM Framework is designed for developers who want to understand and control the entire application lifecycle, from request handling to response rendering.

## CMS Ecosystem

The framework forms the foundation for the DBM Platform, including the CMS Lite module. It is a solution for projects requiring content management without direct file editing.

CMS Lite is a fast, lightweight, and secure website development solution where
files, templates, and routing provide complete control over the system.

For projects requiring content management without direct file editing,
CMS Lite can be extended with CMS Lite + Admin, which adds:

- a browser-based administration panel
- secure authentication
- content editing without code manipulation
- a lightweight architecture

The CMS is delivered as an extension module, allowing it to be installed
in existing projects without the need to rebuild the application.

## When to choose DBM Framework?

This solution is ideal for projects where a standard CMS (like WordPress) is too heavy and inflexible, and a full framework (like Symfony/Laravel) requires too much configuration upfront.

1. **Dedicated applications (SaaS, internal systems)** - when you need a clean architecture and full control over the business logic without the imposed structure of a CMS.
2. **Lightweight websites with a management panel** - when you're building a quick business card or portal, but the client only needs to edit selected sections (thanks to CMS Lite).

3. **High-performance projects** - modular design allows you to load only what's necessary, minimizing system overhead.
4. **Flexible development path (Microservices-ready)** - modular design makes the application future-proof. Logic isolation allows for easy refactoring or splitting specific functionalities into separate services as the project grows.

## Summary

The **DBM Framework** bridges the gap between simple scripts and powerful yet complex enterprise-class frameworks. By choosing this solution, you gain a foundation that doesn't limit your creativity, imposes no unnecessary dependencies, and allows your application to grow in an orderly manner.

This tool was created by developers for developers—where predictability, speed, and code elegance are key.
