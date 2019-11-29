[![Build Status](https://img.shields.io/github/workflow/status/misterjoshua/drupal-memcached/CI)](https://github.com/misterjoshua/drupal-memcached/actions?query=workflow%3ACI)

# Drupal+Memcached Helm Chart

A helm chart for Kubernetes that sets up a Drupal, memcached, and MySQL.

Features:
* Drupal `Deployment`
* Shared `ReadWriteMany` PVC for Drupal files (works with Portworx shared volumes)
* Memcached Deployment
* Single-node MySQL `StatefulSet` with a volume claim template
* Ingress resource with multiple host name support
* Cert-manager `Issuer` and `ClusterIssuer` support (i.e., Letsencrypt/ACME)
* Database settings are automatically configured for Drupal
* Memcached details are auto-configure for Drupal. (Memcached backend is conditionally set based upon detection of the associated drupal module being enabled.)
* MySQL `tinyConfiguration` settings which puts MySQL to minimum requirements
* MySQL password is set via chart values

## Examples

> TODO