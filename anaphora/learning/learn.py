# coding: utf-8

import numpy as np
from sklearn.grid_search import GridSearchCV
from sklearn.metrics import make_scorer
from sklearn.svm import SVC
from sklearn.naive_bayes import GaussianNB

from scorer import score


def svc(X_train, y_train):
    #clf = GridSearchCV(SVC(), scoring=make_scorer(score))
    clf = SVC()
    clf.fit(X_train, y_train)
    print "The best classifier is: ", clf.best_estimator_
    return clf.best_estimator_


def gnb(X_train, y_train):
    clf = GaussianNB()
    clf.fit(X_train, y_train)
    return clf


def load_files():
    train = np.loadtxt('learning.tab')
    test = np.loadtxt('test.tab')

    X_train = train[:, 1:-1]
    y_train = train[:, -1]
    X_test = test[:, 1:-1]
    y_test = test[:, -1]

    # pairs
    names_train = train[:, 0]
    names_test = test[:, 0]

    return X_train, y_train, X_test, y_test, names_train, names_test


if __name__ == '__main__':
    X_train, y_train, X_test, y_test, train, test = load_files()
    estimator = svc(X_train, y_train)

    y_train_predict = estimator.predict(X_train)
    y_test_predict = estimator.predict(X_test)

    print score(y_test_predict)

    np.savetxt('learning.pred.tab', y_train_predict)
    np.savetxt('test.pred.tab', y_test_predict)
