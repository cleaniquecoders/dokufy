# Architecture

This section explains how Dokufy is designed and how its components work together.

## Overview

Dokufy uses the **Driver Pattern** to provide a unified API for document
generation while supporting multiple backends. This allows you to write code
once and switch implementations through configuration.

## Table of Contents

### [1. Overview](01-overview.md)

High-level architecture, component diagram, and design principles.

### [2. Driver Pattern](02-driver-pattern.md)

How the driver system works and how drivers are resolved.

### [3. Processing Flow](03-processing-flow.md)

Step-by-step flow from input to output.

## Related Documentation

- [Drivers](../03-drivers/README.md) - Detailed driver documentation
- [Configuration](../05-configuration/README.md) - Configure drivers and options
