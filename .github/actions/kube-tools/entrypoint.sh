#!/usr/bin/env bash

set -o errexit
set -o pipefail

BIN_DIR="/usr/local/bin"

main() {
  tmpDir=$(mktemp -d)

  pushd $tmpDir >& /dev/null

  KUBECTL=1.18.2
  echo "downloading kubectl ${KUBECTL}"
  curl -sL https://storage.googleapis.com/kubernetes-release/release/v${KUBECTL}/bin/linux/amd64/kubectl \
  -o ${BIN_DIR}//kubectl && chmod +x ${BIN_DIR}//kubectl
  kubectl version --client

  KUSTOMIZE=3.5.5
  echo "downloading kustomize ${KUSTOMIZE}"
  curl -sL https://github.com/kubernetes-sigs/kustomize/releases/download/kustomize%2Fv${KUSTOMIZE}/kustomize_v${KUSTOMIZE}_linux_amd64.tar.gz | \
  tar xz && mv kustomize ${BIN_DIR}//kustomize
  kustomize version

  HELM_V3=3.3.4
  echo "downloading helm ${HELM_V3}"
  curl -sSL https://get.helm.sh/helm-v${HELM_V3}-linux-amd64.tar.gz | \
  tar xz && mv linux-amd64/helm ${BIN_DIR}//helm && rm -rf linux-amd64
  helm version

  KUBEVAL=0.15.0
  echo "downloading kubeval ${KUBEVAL}"
  curl -sL https://github.com/instrumenta/kubeval/releases/download/${KUBEVAL}/kubeval-linux-amd64.tar.gz | \
  tar xz && mv kubeval ${BIN_DIR}//kubeval
  kubeval --version

  KUBEAUDIT=0.11.5
  echo "downloading kubeaudit ${KUBEAUDIT}"
  curl -sSL https://github.com/Shopify/kubeaudit/releases/download/v${KUBEAUDIT}/kubeaudit_${KUBEAUDIT}_linux_amd64.tar.gz | \
  tar xz && mv kubeaudit ${BIN_DIR}/kubeaudit
  kubeaudit --help

  CONFTEST=0.19.0
  echo "downloading conftest ${CONFTEST}"
  curl -sL https://github.com/open-policy-agent/conftest/releases/download/v${CONFTEST}/conftest_${CONFTEST}_Linux_x86_64.tar.gz | \
  tar xz && mv conftest ${BIN_DIR}//conftest
  conftest --version

  KUBESEAL=0.12.5
  echo "downloading kubeseal ${KUBESEAL}"
  curl -sL https://github.com/bitnami-labs/sealed-secrets/releases/download/v${KUBESEAL}/kubeseal-linux-amd64 \
  -o ${BIN_DIR}//kubeseal && chmod +x ${BIN_DIR}//kubeseal
  kubeseal --version

  echo "downloading yq"
  curl -sL https://github.com/mikefarah/yq/releases/latest/download/yq_linux_amd64 \
  -o ${BIN_DIR}//yq && chmod +x ${BIN_DIR}//yq
  yq --version

  echo "downloading jq"
  curl -sL https://github.com/stedolan/jq/releases/latest/download/jq-linux64 \
  -o ${BIN_DIR}//jq && chmod +x ${BIN_DIR}//jq
  jq --version

  popd >& /dev/null
  rm -rf $tmpDir
}

main
